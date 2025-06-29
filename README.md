# Social Portal

A mini PHP-based social media portal where users can:
- 🔐 Register & login
- 👥 Add friends, accept/decline friend requests
- 📝 Create, like, comment, and delete posts
- 💬 Chat with friends in real time
- ⚙️ Edit their profile info & settings

## 🚀 Tech Stack
- PHP 8+
- MySQL
- HTML/CSS + vanilla JS
- XAMPP/WAMP/LAMP (for local hosting)

## 📂 Folder Structure
```
social-portal/
├── assets/
│   └── images/ (default and uploaded images)
├── uploads/ (user-uploaded files)
├── db.php
├── database.sql
├── login.php
├── register.php
├── home.php
├── profile.php
├── post_create.php
├── post_view.php
├── chat_panel.php
├── settings.php
├── like_post.php
├── add_comment.php
├── get_comments.php
├── get_likes.php
├── delete_post.php
├── delete_comment.php
├── delete_message.php
└── ... other supporting files
```

## ⚡ How to Run

1️⃣ **Clone the repo:**  
```bash
git clone https://github.com/raorupeshydv/social-portal.git
cd social-portal
```

2️⃣ **Set up the database:**  
- Import the provided `database.sql` file into your MySQL DB via phpMyAdmin or CLI.
- Make sure your `db.php` connection details (host, user, password, dbname) are correct.

3️⃣ **Start server:**  
- Use XAMPP/WAMP and place the folder inside `C:\xampp\htdocs\`
- Access via: `http://localhost/social-portal/login.php`

4️⃣ **Log in or register a new user** and explore the app!

## ✨ Features
- Login/Register with email/username/phone
- Profile picture uploads & updates
- Friend request, accept, block
- Post with image + text, like, comment, delete
- Chat panel with typing status
- Settings page: update profile info, password, profile pic
- Data cleanup (post deletion removes image, old profile pic removed on update)

## ⚠️ Notes
- Make sure `uploads/` is writable (permissions).
- Tested on PHP 8.2 + MySQL 5.7.
- No external PHP frameworks (pure PHP).

## 📜 License
MIT License - do what you want, just give credit!

---
Enjoy using the Social Portal! 🌟#   s o c i a l - p o r t a l  
 