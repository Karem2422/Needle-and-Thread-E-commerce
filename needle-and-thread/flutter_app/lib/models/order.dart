class Order {
  final int id;
  final String name;
  final String email;
  final String address;
  final double totalPrice;
  final String status;
  final String createdAt;
  final List<OrderItem>? items;

  Order({
    required this.id,
    required this.name,
    required this.email,
    required this.address,
    required this.totalPrice,
    required this.status,
    required this.createdAt,
    this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      address: json['address'],
      totalPrice: double.parse(json['total_price'].toString()),
      status: json['status'],
      createdAt: json['created_at'],
      items: json['items'] != null
          ? (json['items'] as List).map((i) => OrderItem.fromJson(i)).toList()
          : null,
    );
  }
}

class OrderItem {
  final int id;
  final int productId;
  final String title;
  final int quantity;
  final double price;

  OrderItem({
    required this.id,
    required this.productId,
    required this.title,
    required this.quantity,
    required this.price,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productId: json['product_id'],
      title: json['title'] ?? 'Product',
      quantity: json['quantity'],
      price: double.parse(json['price'].toString()),
    );
  }
}
