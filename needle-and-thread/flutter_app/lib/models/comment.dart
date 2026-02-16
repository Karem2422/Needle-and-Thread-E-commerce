class Comment {
  final int id;
  final int productId;
  final String name;
  final String email;
  final String comment;
  final String createdAt;
  final bool approved;

  Comment({
    required this.id,
    required this.productId,
    required this.name,
    required this.email,
    required this.comment,
    required this.createdAt,
    required this.approved,
  });

  factory Comment.fromJson(Map<String, dynamic> json) {
    return Comment(
      id: json['id'],
      productId: json['product_id'],
      name: json['name'],
      email: json['email'],
      comment: json['comment'],
      createdAt: json['created_at'],
      approved: json['approved'] == 1,
    );
  }
}
