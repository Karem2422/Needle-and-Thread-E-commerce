# Needle & Thread Backend API

Complete Node.js + Express + MySQL backend for the Needle & Thread e-commerce mobile application.

## Features

- ✅ JWT Authentication
- ✅ Product Management (CRUD)
- ✅ Order Processing with Inventory Tracking
- ✅ Product Reviews/Comments
- ✅ Admin Dashboard
- ✅ Image Upload Support
- ✅ Input Validation
- ✅ Error Handling
- ✅ CORS Support

## Tech Stack

- **Runtime**: Node.js 18+
- **Framework**: Express.js
- **Database**: MySQL 8.0
- **Authentication**: JWT (jsonwebtoken)
- **Password Hashing**: bcryptjs
- **File Upload**: Multer
- **Validation**: express-validator
- **Security**: Helmet, CORS

## Project Structure

```
backend/
├── config/
│   └── database.js          # MySQL connection pool
├── controllers/
│   ├── authController.js    # Authentication logic
│   ├── productsController.js
│   ├── ordersController.js
│   ├── commentsController.js
│   └── adminController.js
├── middleware/
│   ├── auth.js              # JWT verification
│   └── errorHandler.js
├── routes/
│   ├── auth.js
│   ├── products.js
│   ├── orders.js
│   ├── comments.js
│   └── admin.js
├── migrations/
│   └── 001_create_tables.sql
├── uploads/                 # Product images
├── .env                     # Environment variables
├── .env.example
├── package.json
└── server.js                # Entry point
```

## Installation

### 1. Install Dependencies

```bash
cd backend
npm install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and update the values:

```bash
cp .env.example .env
```

Edit `.env`:
```env
NODE_ENV=development
PORT=3000

DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=needle_thread

JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_EXPIRES_IN=7d

MAX_FILE_SIZE=5242880
UPLOAD_PATH=./uploads

CORS_ORIGIN=*
```

### 3. Set Up Database

Run the migration SQL file in MySQL:

```bash
# Using MySQL command line
mysql -u root -p < migrations/001_create_tables.sql

# Or import via phpMyAdmin
```

This will create:
- Database: `needle_thread`
- 10 tables with relationships
- Sample admin user and products

### 4. Create Admin User

The migration creates a default admin user. To set a proper password, run this SQL:

```sql
-- Generate password hash for 'admin123'
-- Use bcrypt online tool or Node.js to generate hash
UPDATE users 
SET password_hash = '$2a$10$YourActualBcryptHashHere' 
WHERE email = 'admin@needlethread.com';
```

Or use this Node.js script:

```javascript
const bcrypt = require('bcryptjs');
bcrypt.hash('admin123', 10).then(hash => console.log(hash));
```

## Running the Server

### Development Mode (with auto-reload)

```bash
npm run dev
```

### Production Mode

```bash
npm start
```

The server will start on `http://localhost:3000`

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| GET | `/api/auth/me` | Get current user profile | Yes |
| POST | `/api/auth/forgot-password` | Request password reset | No |
| POST | `/api/auth/reset-password` | Reset password | No |

### Products

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/products` | Get all products (with filters) | No |
| GET | `/api/products/:id` | Get single product | No |
| POST | `/api/products` | Create product | Admin |
| PUT | `/api/products/:id` | Update product | Admin |
| DELETE | `/api/products/:id` | Delete product | Admin |
| POST | `/api/products/:id/images` | Upload product images | Admin |
| DELETE | `/api/products/:id/images/:imageId` | Delete product image | Admin |

### Orders

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/orders` | Get orders (user: own, admin: all) | Yes |
| GET | `/api/orders/:id` | Get order details | Yes |
| POST | `/api/orders` | Create new order | Yes |
| PUT | `/api/orders/:id/status` | Update order status | Admin |
| GET | `/api/orders/:id/history` | Get order status history | Yes |

### Comments/Reviews

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/products/:productId/comments` | Get product comments | No |
| POST | `/api/products/:productId/comments` | Add comment | Yes |
| PUT | `/api/comments/:id/approve` | Approve comment | Admin |
| DELETE | `/api/comments/:id` | Delete comment | Admin |
| GET | `/api/admin/comments` | Get all comments | Admin |

### Admin

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/admin/stats` | Get dashboard statistics | Admin |
| GET | `/api/admin/users` | Get all users | Admin |
| GET | `/api/admin/inventory-logs` | Get inventory logs | Admin |
| PUT | `/api/admin/users/:id/toggle-admin` | Toggle user admin status | Admin |

## Authentication

The API uses JWT (JSON Web Tokens) for authentication.

### Login Flow

1. **Register/Login**: Send credentials to `/api/auth/login`
2. **Receive Token**: Get JWT token in response
3. **Use Token**: Include token in Authorization header for protected routes

```javascript
// Login request
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "isAdmin": false
    }
  }
}

// Use token in subsequent requests
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

## Request/Response Examples

### Create Order

```javascript
POST /api/orders
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "address": "123 Main St, City, Country",
  "phone": "1234567890",
  "items": [
    {
      "productId": 1,
      "quantity": 2,
      "price": 189.99
    },
    {
      "productId": 3,
      "quantity": 1,
      "price": 95.00
    }
  ],
  "totalPrice": 484.98,
  "paymentStatus": "paid"
}
```

### Upload Product Images

```javascript
POST /api/products/1/images
Authorization: Bearer <admin-token>
Content-Type: multipart/form-data

images: [File, File, File]
```

### Filter Products

```javascript
GET /api/products?search=tote&minPrice=100&maxPrice=200&status=available&page=1&limit=9
```

## Error Handling

All errors follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation error)
- `401` - Unauthorized (no token or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `500` - Internal Server Error

## File Upload

Product images are stored in the `uploads/` directory and served statically at `/uploads/:filename`.

**Supported formats**: JPEG, JPG, PNG, GIF, WebP  
**Max file size**: 5MB (configurable in `.env`)  
**Max files per upload**: 5

## Security Features

- ✅ JWT token authentication
- ✅ Bcrypt password hashing (10 salt rounds)
- ✅ Helmet security headers
- ✅ CORS configuration
- ✅ Input validation and sanitization
- ✅ SQL injection prevention (parameterized queries)
- ✅ File upload restrictions

## Testing

### Manual Testing with cURL

```bash
# Register user
curl -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Get products
curl http://localhost:3000/api/products

# Get products with auth
curl http://localhost:3000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Testing with Postman

1. Import the API endpoints
2. Create environment variables for `baseUrl` and `token`
3. Test each endpoint with sample data

## Deployment

### Heroku

```bash
# Login to Heroku
heroku login

# Create app
heroku create needle-thread-api

# Set environment variables
heroku config:set NODE_ENV=production
heroku config:set JWT_SECRET=your-production-secret
heroku config:set DB_HOST=your-db-host
heroku config:set DB_USER=your-db-user
heroku config:set DB_PASSWORD=your-db-password
heroku config:set DB_NAME=needle_thread

# Deploy
git push heroku main
```

### DigitalOcean / AWS

1. Set up MySQL database
2. Upload code to server
3. Install dependencies: `npm install --production`
4. Set environment variables
5. Run migrations
6. Start server with PM2: `pm2 start server.js`

## Troubleshooting

### Database Connection Error

```
❌ Database connection failed: Access denied for user
```

**Solution**: Check DB credentials in `.env` file

### Port Already in Use

```
Error: listen EADDRINUSE: address already in use :::3000
```

**Solution**: Change PORT in `.env` or kill process using port 3000

### File Upload Error

```
Error: ENOENT: no such file or directory, open './uploads/...'
```

**Solution**: Ensure `uploads/` directory exists (created automatically on server start)

## License

MIT

## Support

For issues or questions, please contact the development team.
