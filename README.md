# Theme Add-ons

A lightweight collection of WordPress add-ons developed by Hostbot to enhance theme development and streamline admin workflows.

---

## Included Add-ons

### 🔁 User Switching (`userswitching.php`)
Allows **administrators only** to switch into another user account, then switch back seamlessly.

- Adds “Switch to User” links in the Users list.
- Stores original admin ID in a secure cookie.
- Adds “Switch Back” link in the WordPress admin bar.
- Protected with nonces for secure action handling.
- Ideal for development, support, or staging environments.

### 📄 Duplicate Post (`duplicatepost.php`)
Adds a “Duplicate” link to **posts, pages, and all custom post types**, including Gutenberg’s **reusable blocks**.

- Clones post content, excerpt, metadata, and custom fields.
- Generates the duplicate as a **draft** with "(Copy)" appended to the title.
- Regenerates Elementor CSS if Elementor is installed.
- Nonce-protected and visible to users with post edit permissions.

---

## Usage

1. Copy the `.php` files into your active theme directory (or include them via a custom plugin).
2. No configuration needed — each add-on runs automatically when included.

---

## License

GPL-2.0+ — Same license as WordPress. Feel free to adapt, extend, or fork.

---

## Author

Maintained by [Hostbot](https://hostbot.io/) under the `hostbot-hub` GitHub account.

