const express = require('express');
const router = express.Router();

// Main dashboard route
router.get('/', (req, res) => {
    // Check if user is logged in
    if (!req.session.userId) {
        return res.redirect('/auth/login');
    }
    
    // Render dashboard based on user role
    switch(req.session.userRole) {
        case 'admin':
            res.render('admin/dashboard');
            break;
        case 'department_head':
            res.render('head/dashboard');
            break;
        default:
            res.render('employee/dashboard');
    }
});

// Public routes
router.get('/about', (req, res) => {
    res.render('public/about');
});

router.get('/contact', (req, res) => {
    res.render('public/contact');
});

module.exports = router;
