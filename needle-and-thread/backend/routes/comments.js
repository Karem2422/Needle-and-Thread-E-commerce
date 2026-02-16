const express = require('express');
const router = express.Router();
const { body } = require('express-validator');
const commentsController = require('../controllers/commentsController');
const { verifyToken, requireAdmin, optionalAuth } = require('../middleware/auth');

// Validation rules
const commentValidation = [
    body('name').trim().notEmpty().withMessage('Name is required'),
    body('email').isEmail().withMessage('Valid email is required'),
    body('comment').trim().notEmpty().withMessage('Comment is required')
];

// Routes
router.get('/products/:productId/comments', optionalAuth, commentsController.getComments);
router.post('/products/:productId/comments', verifyToken, commentValidation, commentsController.addComment);
router.put('/comments/:id/approve', verifyToken, requireAdmin, commentsController.approveComment);
router.delete('/comments/:id', verifyToken, requireAdmin, commentsController.deleteComment);
router.get('/admin/comments', verifyToken, requireAdmin, commentsController.getAllComments);

module.exports = router;
