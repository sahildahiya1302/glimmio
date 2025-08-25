const express = require('express');
const router = express.Router();
const db = require('../config/database');

// Middleware to check if user is admin
function isAdmin(req, res, next) {
    if (req.session.userRole !== 'admin') {
        return res.redirect('/');
    }
    next();
}

// Admin dashboard
router.get('/dashboard', isAdmin, async (req, res) => {
    try {
        // Get dashboard data
        const [totalLeads] = await db.execute('SELECT COUNT(*) as count FROM leads');
        const [assignedLeads] = await db.execute('SELECT COUNT(*) as count FROM leads WHERE status != "unassigned"');
        const [unassignedLeads] = await db.execute('SELECT COUNT(*) as count FROM leads WHERE status = "unassigned"');
        const [totalTasks] = await db.execute('SELECT COUNT(*) as count FROM tasks');
        const [pendingTasks] = await db.execute('SELECT COUNT(*) as count FROM tasks WHERE status = "pending"');
        const [completedTasks] = await db.execute('SELECT COUNT(*) as count FROM tasks WHERE status = "completed"');
        
        res.render('admin/dashboard', {
            totalLeads: totalLeads[0].count,
            assignedLeads: assignedLeads[0].count,
            unassignedLeads: unassignedLeads[0].count,
            totalTasks: totalTasks[0].count,
            pendingTasks: pendingTasks[0].count,
            completedTasks: completedTasks[0].count,
            username: req.session.username
        });
    } catch (err) {
        console.error(err);
        res.redirect('/');
    }
});

// Manage users
router.get('/users', isAdmin, async (req, res) => {
    try {
        const [users] = await db.execute('SELECT * FROM users');
        res.render('admin/users', { users });
    } catch (err) {
        console.error(err);
        res.redirect('/admin/dashboard');
    }
});

// Add user
router.post('/users/add', isAdmin, async (req, res) => {
    const { username, email, role, password } = req.body;
    
    try {
        // Hash password
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);
        
        // Insert user
        await db.execute(
            'INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)',
            [username, email, role, hashedPassword]
        );
        
        res.redirect('/admin/users');
    } catch (err) {
        console.error(err);
        res.redirect('/admin/users');
    }
});

// Manage leads
router.get('/leads', isAdmin, async (req, res) => {
    try {
        const [leads] = await db.execute(`
            SELECT l.*, u.username as assigned_to_name 
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id
        `);
        const [users] = await db.execute('SELECT id, username FROM users');
        
        res.render('admin/leads', { leads, users });
    } catch (err) {
        console.error(err);
        res.redirect('/admin/dashboard');
    }
});

// Add lead
router.post('/leads/add', isAdmin, async (req, res) => {
    const { name, contact_info, service_type, remarks } = req.body;
    
    try {
        await db.execute(
            'INSERT INTO leads (name, contact_info, service_type, remarks, status) VALUES (?, ?, ?, ?, ?)',
            [name, contact_info, service_type, remarks, 'unassigned']
        );
        
        res.redirect('/admin/leads');
    } catch (err) {
        console.error(err);
        res.redirect('/admin/leads');
    }
});

// Assign lead
router.post('/leads/assign/:id', isAdmin, async (req, res) => {
    const { assigned_to } = req.body;
    
    try {
        await db.execute(
            'UPDATE leads SET assigned_to = ?, status = ? WHERE id = ?',
            [assigned_to, 'working', req.params.id]
        );
        
        res.redirect('/admin/leads');
    } catch (err) {
        console.error(err);
        res.redirect('/admin/leads');
    }
});

module.exports = router;
