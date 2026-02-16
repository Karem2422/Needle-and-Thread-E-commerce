const express = require('express');
const router = express.Router();
const { body } = require('express-validator');
const multer = require('multer');
const path = require('path');
const productsController = require('../controllers/productsController');
const { verifyToken, requireAdmin, optionalAuth } = require('../middleware/auth');

// Configure multer for image uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, process.env.UPLOAD_PATH || './uploads');
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});

const fileFilter = (req, file, cb) => {
    const allowedTypes = /jpeg|jpg|png|gif|webp/;
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes.test(file.mimetype);

    if (extname && mimetype) {
        cb(null, true);
    } else {
        cb(new Error('Only image files are allowed (jpeg, jpg, png, gif, webp)'));
    }
};

const upload = multer({
    storage,
    fileFilter,
    limits: {
        fileSize: parseInt(process.env.MAX_FILE_SIZE) || 5 * 1024 * 1024 // 5MB default
    }
});

// Validation rules
const productValidation = [
    body('title').trim().notEmpty().withMessage('Title is required'),
    body('slug').trim().notEmpty().withMessage('Slug is required'),
    body('description').trim().notEmpty().withMessage('Description is required'),
    body('price').isFloat({ min: 0 }).withMessage('Price must be a positive number'),
    body('quantity').isInt({ min: 0 }).withMessage('Quantity must be a non-negative integer')
];

// Routes
router.get('/', optionalAuth, productsController.getProducts);
router.get('/:id', optionalAuth, productsController.getProductById);
router.post('/', verifyToken, requireAdmin, productValidation, productsController.createProduct);
router.put('/:id', verifyToken, requireAdmin, productsController.updateProduct);
router.delete('/:id', verifyToken, requireAdmin, productsController.deleteProduct);
router.post('/:id/images', verifyToken, requireAdmin, upload.array('images', 5), productsController.uploadImages);
router.delete('/:id/images/:imageId', verifyToken, requireAdmin, productsController.deleteImage);

module.exports = router;
