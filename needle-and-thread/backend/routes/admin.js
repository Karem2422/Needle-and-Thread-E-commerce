const express = require('express');
const router = express.Router();
const adminController = require('../controllers/adminController');
const { verifyToken, requireAdmin } = require('../middleware/auth');

// All admin routes require authentication and admin role
router.use(verifyToken);
router.use(requireAdmin);

// Routes
router.get('/stats', adminController.getStats);
router.get('/users', adminController.getUsers);
router.get('/inventory-logs', adminController.getInventoryLogs);
router.put('/users/:id/toggle-admin', adminController.toggleAdminStatus);

module.exports = router;
