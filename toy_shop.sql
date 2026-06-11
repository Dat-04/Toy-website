-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2025 at 01:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toy_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `username`, `password`, `email`, `status`, `created_at`) VALUES
(1, 'tdat247@gmail.com', '$2y$10$70gULnwWWtTToAI6CBRvZ..OmSeTSgR/iZH8zoG0e80dt.CWUkWie', 'tdat247@gmail.com', 'active', '2025-07-10 03:45:22'),
(2, 'dtrungcx8@gmail.com', '$2y$10$MlwThFh5sz1J/jdsxpKtOeHkAYqk8Hj6m5PqV95tSJo7XiehORuu6', 'dtrungcx8@gmail.com', 'active', '2025-07-23 07:39:13');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `full_name`, `avatar`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@toyshop.com', 'Admin', NULL, '2025-07-10 03:37:52'),
(5, 'dat', 'datto', 'dat@gmail.com', 'to dat', 'j.jpg', '2025-07-10 04:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `image`, `link`, `position`, `status`, `created_at`) VALUES
(1, 'sale.jpg', 'products.php', 1, 'active', '2025-07-10 03:37:52'),
(2, 'gaubong.webp', 'products.php', 2, 'active', '2025-07-10 03:37:52'),
(3, 'sale.webp', 'products.php', 3, 'active', '2025-07-10 03:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `product_id`, `quantity`, `created_at`) VALUES
(12, 1, 1, 1, '2025-07-15 16:16:31'),
(13, 1, 2, 1, '2025-07-15 16:16:32'),
(14, 1, 5, 1, '2025-07-15 16:16:34'),
(15, 1, 3, 1, '2025-07-20 09:08:50'),
(22, 2, 1, 1, '2025-07-25 11:18:10');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Đồ chơi trẻ em', 'Các loại đồ chơi dành cho trẻ em', 'dochoi.jpg', 'active', '2025-07-10 03:37:52'),
(2, 'Đồ chơi giáo dục', 'Đồ chơi phát triển trí tuệ', 'dochoigd.jpg', 'active', '2025-07-10 03:37:52'),
(3, 'Đồ chơi điện tử', 'Các loại đồ chơi điện tử hiện đại', 'dientu.jpg', 'active', '2025-07-10 03:37:52'),
(4, 'Búp bê', 'Các loại búp bê và phụ kiện', 'bupbe.jpg', 'active', '2025-07-10 03:37:52'),
(5, 'Xe đồ chơi', 'Ô tô, xe máy đồ chơi', 'xedochoi.jpg', 'active', '2025-07-10 03:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `sender_type` enum('customer','admin') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `customer_id`, `admin_id`, `message`, `sender_type`, `is_read`, `created_at`) VALUES
(1, 1, NULL, 'hiii', 'customer', 0, '2025-07-10 14:53:39'),
(2, 1, NULL, 'xin chào', 'customer', 0, '2025-07-20 05:48:16'),
(3, 1, NULL, 'mình muốn hỏi', 'customer', 0, '2025-07-20 09:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `account_id`, `full_name`, `email`, `phone`, `address`, `avatar`, `date_of_birth`, `gender`, `created_at`) VALUES
(1, 1, 'Tô Đạt', 'tdat247@gmail.com', '0904911172', 'THON PHI LIET, LIEN NGHIA, VAN GIANG, HU NG YEN', '1752160781_HUNGYEN.jpg', '0000-00-00', '', '2025-07-10 03:45:22'),
(2, 2, 'trung dương', 'dtrungcx8@gmail.com', '123232323', '100 Đông Thiên', NULL, '2006-02-18', 'male', '2025-07-23 07:39:13');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` enum('percentage','fixed') DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `code`, `name`, `type`, `value`, `min_order_amount`, `max_uses`, `used_count`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 'SALE10', 'Giảm 10%', 'percentage', 10.00, 100000.00, 100, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(2, 'SALE20', 'Giảm 20%', 'percentage', 20.00, 200000.00, 50, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(3, 'SALE30', 'Giảm 30%', 'percentage', 30.00, 300000.00, 30, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(4, 'FREESHIP', 'Miễn phí vận chuyển', 'fixed', 30000.00, 150000.00, 200, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(5, 'SUMMER50', 'Giảm 50k hè', 'fixed', 50000.00, 400000.00, 100, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(6, 'VIP100', 'Khách VIP giảm 100k', 'fixed', 100000.00, 1000000.00, 10, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40'),
(7, 'NEWUSER', 'Giảm 15% cho khách mới', 'percentage', 15.00, 0.00, 500, 0, '2025-07-01', '2025-12-31', 'active', '2025-07-23 09:30:40'),
(8, 'FLASH5', 'Flash Sale 5%', 'percentage', 5.00, 50000.00, 1000, 0, '2025-07-20', '2025-07-25', 'active', '2025-07-23 09:30:40'),
(9, 'BACK2SCHOOL', 'Back to School giảm 40k', 'fixed', 40000.00, 250000.00, 300, 0, '2025-08-01', '2025-08-31', 'active', '2025-07-23 09:30:40'),
(10, 'BIRTHDAY', 'Sinh nhật giảm 25%', 'percentage', 25.00, 0.00, 100, 0, '2025-07-01', '2025-07-31', 'active', '2025-07-23 09:30:40');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `image`, `status`, `created_at`) VALUES
(1, 'Ra mắt bộ Lego siêu anh hùng mới', 'Cửa hàng vừa cập nhật bộ Lego chủ đề siêu anh hùng với hơn 500 chi tiết lắp ráp thú vị cho bé.', 'lego.jpg', 'active', '2025-07-20 09:20:19'),
(2, 'Khuyến mãi 30% cho đồ chơi trí tuệ tuần này', 'Chương trình khuyến mãi kéo dài từ 15/8 đến 21/8, áp dụng cho tất cả đồ chơi phát triển trí tuệ.', 'km.jpg', 'active', '2025-07-20 09:20:19'),
(4, 'Gợi ý quà tặng cho bé yêu', 'Top 10 món đồ chơi được yêu thích nhất dành tặng bé trai và bé gái nhân dịp Quốc tế Thiếu nhi.', 'qt.jpg', 'active', '2025-07-20 09:20:19'),
(5, 'Thông báo nghỉ lễ và lịch giao hàng', 'Cửa hàng nghỉ lễ từ 1/9 đến 3/9. Các đơn hàng sẽ được xử lý lại từ ngày 4/9. Mong quý khách thông cảm.', 'tb.jpg', 'active', '2025-07-20 09:20:19'),
(6, 'Đồ chơi điều khiển từ xa mới về', 'Nhiều mẫu ô tô và máy bay điều khiển từ xa vừa được nhập khẩu, chất lượng cao, giá tốt.', '1753004546_rc.jpg', 'active', '2025-07-20 09:42:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('order','system','promotion') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710175623967 đã được cập nhật trạng thái: Đang giao hàng', 'order', 0, '2025-07-11 02:50:19'),
(2, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710175623967 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-11 02:57:21'),
(3, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710175623967 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-11 02:57:59'),
(4, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250711051758277 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-11 03:18:19'),
(5, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710174909567 đã được cập nhật trạng thái: Đã xác nhận', 'order', 0, '2025-07-11 03:27:47'),
(6, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250711051758277 đã được cập nhật trạng thái: pending', 'order', 0, '2025-07-11 03:28:46'),
(7, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250711051758277 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-11 03:28:51'),
(8, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710173902610 đã được cập nhật trạng thái: Đã xác nhận', 'order', 0, '2025-07-11 03:42:12'),
(9, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710174909567 đã được cập nhật trạng thái: Đang giao hàng', 'order', 0, '2025-07-11 03:42:17'),
(10, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710172455198 đã được cập nhật trạng thái: Đã xác nhận', 'order', 0, '2025-07-11 03:47:10'),
(11, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710174909567 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-11 04:05:08'),
(12, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710173902610 đã được cập nhật trạng thái: Đang giao hàng', 'order', 0, '2025-07-11 04:05:17'),
(13, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710171912951 đã được cập nhật trạng thái: Đang giao hàng', 'order', 0, '2025-07-20 09:16:50'),
(14, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250723122737985 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-23 10:33:28'),
(15, 'Cập nhật đơn hàng', 'Đơn hàng #ORD20250710171912951 đã được cập nhật trạng thái: Đã giao hàng', 'order', 0, '2025-07-23 18:25:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_code` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_code`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_address`, `notes`, `created_at`, `discount_id`) VALUES
(1, 1, 'ORD20250710171912951', 358900.00, 'delivered', 'cod', 'paid', 'hưng yên', '', '2025-07-10 15:19:12', NULL),
(2, 1, 'ORD20250710172455198', 248900.00, 'confirmed', 'cod', 'paid', 'hưng yên', '', '2025-07-10 15:24:55', NULL),
(3, 1, 'ORD20250710173902610', 468900.00, 'shipping', 'vnpay', 'paid', 'hy', '', '2025-07-10 15:39:02', NULL),
(4, 1, 'ORD20250710174909567', 171900.00, 'delivered', 'momo', 'paid', 'hu', '', '2025-07-10 15:49:09', NULL),
(5, 1, 'ORD20250710175623967', 689700.00, 'delivered', 'bank_transfer', 'paid', 'ẻ', 'frg', '2025-07-10 15:56:23', NULL),
(6, 1, 'ORD20250711051758277', 171900.00, 'delivered', 'cod', 'paid', 'THON PHI LIET, LIEN NGHIA, VAN GIANG, HU NG YEN', '', '2025-07-11 03:17:58', NULL),
(7, 2, 'ORD20250723122737985', 942700.00, 'delivered', 'vnpay', 'paid', '100 Đông Thiên', '', '2025-07-23 10:27:37', NULL),
(8, 2, 'ORD20250725131628363', 467800.00, 'pending', 'cod', 'pending', '100 Đông Thiên', '', '2025-07-25 11:16:28', NULL),
(9, 2, 'ORD20250725131719507', 358900.00, 'pending', 'cod', 'pending', '100 Đông Thiên', '', '2025-07-25 11:17:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `total`) VALUES
(1, 1, 1, 1, 299000.00, 299000.00),
(2, 2, 2, 1, 199000.00, 199000.00),
(3, 3, 3, 1, 399000.00, 399000.00),
(4, 4, 5, 1, 129000.00, 129000.00),
(5, 5, 1, 1, 299000.00, 299000.00),
(6, 5, 2, 1, 199000.00, 199000.00),
(7, 5, 5, 1, 129000.00, 129000.00),
(8, 6, 5, 1, 129000.00, 129000.00),
(9, 7, 3, 1, 399000.00, 399000.00),
(10, 7, 1, 1, 299000.00, 299000.00),
(11, 7, 4, 1, 159000.00, 159000.00),
(12, 8, 2, 2, 199000.00, 398000.00),
(13, 9, 1, 1, 299000.00, 299000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `transaction_info` text DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `transaction_id`, `transaction_info`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 3, 'vnpay', 468900.00, NULL, NULL, 'pending', '2025-07-10 15:39:02', '2025-07-10 15:39:02'),
(2, 4, 'momo', 171900.00, NULL, NULL, 'pending', '2025-07-10 15:49:09', '2025-07-10 15:49:09'),
(3, 5, 'bank_transfer', 689700.00, NULL, NULL, 'pending', '2025-07-10 15:56:23', '2025-07-10 15:56:23'),
(4, 6, 'cod', 171900.00, NULL, NULL, 'pending', '2025-07-11 03:17:58', '2025-07-11 03:17:58'),
(5, 7, 'vnpay', 942700.00, NULL, NULL, 'pending', '2025-07-23 10:27:37', '2025-07-23 10:27:37'),
(6, 8, 'cod', 467800.00, NULL, NULL, 'pending', '2025-07-25 11:16:28', '2025-07-25 11:16:28'),
(7, 9, 'cod', 358900.00, NULL, NULL, 'pending', '2025-07-25 11:17:19', '2025-07-25 11:17:19');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `sale_price`, `category_id`, `image`, `stock_quantity`, `status`, `featured`, `created_at`) VALUES
(1, 'Robot Transformer', 'Robot biến hình siêu ngầu cho bé', 299000.00, NULL, 5, '1.jpg', 46, 'active', 1, '2025-07-10 03:37:52'),
(2, 'Búp bê Barbie', 'Búp bê Barbie xinh đẹp với trang phục thời trang', 199000.00, NULL, 4, 'barbie1.jpg', 26, 'active', 1, '2025-07-10 03:37:52'),
(3, 'Xe ô tô điều khiển', 'Xe ô tô điều khiển từ xa tốc độ cao', 399000.00, NULL, 5, 'car.jpg', 9, 'active', 1, '2025-07-10 03:37:52'),
(4, 'Lego Classic', 'Bộ lego cơ bản phát triển sáng tạo', 159000.00, NULL, 2, 'lego.webp', 39, 'active', 0, '2025-07-10 03:37:52'),
(5, 'Gấu bông Teddy', 'Gấu bông mềm mại đáng yêu', 129000.00, NULL, 1, 'teddy.jpg', 57, 'active', 1, '2025-07-10 03:37:52'),
(6, 'Máy xúc điều khiển từ xa đồ chơi cho bé', 'Máy xúc 11 kênh xe múc đất cát điều khiển từ xa gầu sắt hợp kim\r\nMẫu gầu sắt 11 kênh pin sạc:\r\n- Mã : 11 kênh -  2.4G - Size lớn\r\n- Pin : pin sạc 3.7V cực mạnh\r\n- Điều khiển : Dạng tay cầm,  2.4Ghz, Không nhiễu sóng điều khiển\r\n- Màu sắc : vàng\r\n- Chât liệu : Gầu xúc hợp kim, nắp máy hợp kim, Vỏ nhựa cao cấp - An toàn với trẻ nhỏ\r\n\r\n- Size:  thân 30x10.5x25 cm\r\n- Kích thước hộp : 35.8 x 13.3 x  16.5 cm\r\n- Chức năng: Tiến, lùi; múc lên, xuống; rẽ phải, trái; xoay mâm phải, trái 680 độ; chế độ tự động trình diễn, âm thanh động cơ, đèn pha trước, ...\r\n- Sản phẩm bao gồm 1 xe múc, 1 điều khiển,1 pin sạc,1 USB sạc, 1 tua vít, hdsd và hộp đựng.\r\nChắc chắn đây là món quà đáng nhớ của tuổi thơ các em.', 249000.00, 230000.00, 5, '1753001233_mayxuc.webp', 20, 'active', 0, '2025-07-20 08:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `customer_id`, `order_id`, `rating`, `comment`, `images`, `admin_reply`, `status`, `created_at`) VALUES
(1, 1, 1, 5, 5, 'tuyệt', '[]', NULL, 'approved', '2025-07-11 02:58:33'),
(2, 2, 1, 5, 5, 'ok', '[]', NULL, 'approved', '2025-07-11 02:58:41'),
(3, 5, 1, 5, 5, 'gấu bông đẹp lắm', '[]', NULL, 'approved', '2025-07-11 02:59:11'),
(5, 3, 2, 7, 5, 'cháu nhà mình thích lắm', '[\"1753268197_ok.jpg\"]', NULL, 'approved', '2025-07-23 10:56:37'),
(6, 1, 2, 7, 5, 'ok', '[]', NULL, 'approved', '2025-07-23 10:56:51'),
(7, 4, 2, 7, 5, 'ok', '[]', NULL, 'approved', '2025-07-23 10:57:03');

-- --------------------------------------------------------

--
-- Table structure for table `top_menu`
--

CREATE TABLE `top_menu` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `top_menu`
--

INSERT INTO `top_menu` (`id`, `name`, `link`, `parent_id`, `position`, `status`) VALUES
(1, 'Trang chủ', 'index.php', 0, 1, 'active'),
(2, 'Sản phẩm', 'products.php', 0, 2, 'active'),
(3, 'Tin tức', 'news.php', 0, 3, 'active'),
(4, 'Liên hệ', 'contact.php', 0, 4, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `customer_id`, `product_id`, `created_at`) VALUES
(2, 1, 5, '2025-07-10 15:43:06'),
(4, 1, 4, '2025-07-17 12:41:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `top_menu`
--
ALTER TABLE `top_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `top_menu`
--
ALTER TABLE `top_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
