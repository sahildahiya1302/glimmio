const express = require('express');
const router = express.Router();
const db = require('../config/database');

// Middleware to check if user is authenticated
function isAuthenticated(req, res, next) {
    if (!req.session.userId) {
        return res.redirect('/auth/login');
    }
    next();
}

// Employee dashboard
router.get('/dashboard', isAuthenticated, async (req, res) => {
    try {
        // Get assigned leads count
        const [leadsCount] = await db.execute(
            'SELECT COUNT(*) as count FROM leads WHERE assigned_to = ?',
            [req.session.userId]
        );
        
        // Get assigned tasks count
        const [tasksCount] = await db.execute(
            'SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?',
            [req.session.userId]
        );
        
        // Get pending tasks
        const [pendingTasks] = await db.execute(
            'SELECT * FROM tasks WHERE assigned_to = ? AND status = "pending"',
            [req.session.userId]
        );
        
        res.render('employee/dashboard', {
            leadsCount: leadsCount[0].count,
            tasksCount: tasksCount[0].count,
            pendingTasks: pendingTasks
        });
    } catch (err) {
        console.error(err);
        res.redirect('/');
    }
});

// Mark attendance
router.post('/attendance', isAuthenticated, async (req, res) => {
    try {
        await db.execute(
            'INSERT INTO attendance (employee_id, date, login_time) VALUES (?, CURDATE(), NOW())',
            [req.session.userId]
        );
        
        res.redirect('/employees/dashboard');
    } catch (err) {
        console.error(err);
        res.redirect('/employees/dashboard');
    }
});

// View attendance history
router.get('/attendance', isAuthenticated, async (req, res) => {
    try {
        const [attendance] = await db.execute(
            'SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC',
            [req.session.userId]
        );
        
        res.render('employee/attendance', { attendance });
    } catch (err) {
        console.error(err);
        res.redirect('/employees/dashboard');
    }
});

// Submit suggestion
router.post('/suggestion', isAuthenticated, async (req, res) => {
    const { content } = req.body;
    
    try {
        await db.execute(
            'INSERT INTO suggestions (employee_id, content, anonymous) VALUES (?, ?, ?)',
            [req.session.userId, content, 1]
        );
        
        res.redirect('/employees/dashboard');
    } catch (err) {
        console.error(err);
        res.redirect('/employees/dashboard');
    }
});

module.exports = router;
