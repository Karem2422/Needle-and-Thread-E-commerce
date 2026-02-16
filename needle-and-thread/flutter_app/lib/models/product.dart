class Product {
  final int id;
  final String title;
  final String slug;
  final String description;
  final double price;
  final int quantity;
  final String status;
  final String? tags;
  final String? primaryImage;
  final List<ProductImage>? images;

  Product({
    required this.id,
    required this.title,
    required this.slug,
    required this.description,
    required this.price,
    required this.quantity,
    required this.status,
    this.tags,
    this.primaryImage,
    this.images,
  });

  bool get isAvailable => status == 'available' && quantity > 0;

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      title: json['title'],
      slug: json['slug'],
      description: json['description'],
      price: double.parse(json['price'].toString()),
      quantity: json['quantity'],
      status: json['status'],
      tags: json['tags'],
      primaryImage: json['primary_image'] ?? json['image_filename'],
      images: json['images'] != null
          ? (json['images'] as List).map((i) => ProductImage.fromJson(i)).toList()
          : null,
    );
  }
}

class ProductImage {
  final int id;
  final String filename;
  final bool isPrimary;

  ProductImage({
    required this.id,
    required this.filename,
    required this.isPrimary,
  });

  factory ProductImage.fromJson(Map<String, dynamic> json) {
    return ProductImage(
      id: json['id'],
      filename: json['filename'],
      isPrimary: json['is_primary'] == 1,
    );
  }
}
