class CartItem {
  final int productId;
  final String title;
  final double price;
  int quantity;
  final String? image;

  CartItem({
    required this.productId,
    required this.title,
    required this.price,
    required this.quantity,
    this.image,
  });

  double get total => price * quantity;

  Map<String, dynamic> toJson() {
    return {
      'productId': productId,
      'title': title,
      'price': price,
      'quantity': quantity,
      'image': image,
    };
  }

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      productId: json['productId'],
      title: json['title'],
      price: double.parse(json['price'].toString()),
      quantity: json['quantity'],
      image: json['image'],
    );
  }
}
