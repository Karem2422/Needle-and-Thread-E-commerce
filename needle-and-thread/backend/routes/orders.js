const express = require('express');
const router = express.Router();
const { body } = require('express-validator');
const ordersController = require('../controllers/ordersController');
const { verifyToken, requireAdmin } = require('../middleware/auth');

// Validation rules
const orderValidation = [
    body('name').trim().notEmpty().withMessage('Name is required'),
    body('email').isEmail().withMessage('Valid email is required'),
    body('address').trim().notEmpty().withMessage('Address is required'),
    body('items').isArray({ min: 1 }).withMessage('Order must contain at least one item'),
    body('totalPrice').isFloat({ min: 0 }).withMessage('Total price must be a positive number')
];

// Routes
router.get('/', verifyToken, ordersController.getOrders);
router.get('/:id', verifyToken, ordersController.getOrderById);
router.post('/', verifyToken, orderValidation, ordersController.createOrder);
router.put('/:id/status', verifyToken, requireAdmin, ordersController.updateOrderStatus);
router.get('/:id/history', verifyToken, ordersController.getOrderHistory);

module.exports = router;
