-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Апр 27 2026 г., 12:33
-- Версия сервера: 8.0.43-0ubuntu0.22.04.2
-- Версия PHP: 8.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `reg`
--

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id_order` int NOT NULL,
  `user_id` int NOT NULL,
  `address` text NOT NULL,
  `order_price` decimal(10,2) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `recipient_name` varchar(250) DEFAULT NULL,
  `recipient_phone` varchar(100) DEFAULT NULL,
  `status` enum('new','assembling','shipped','delivered') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id_order`, `user_id`, `address`, `order_price`, `delivery_date`, `card_number`, `recipient_name`, `recipient_phone`, `status`, `created_at`) VALUES
(1, 3, 'Уралхиммаш', '1.00', '2026-04-15', '1234 5678 9012 3456', 'devnyash', '+7123456789', 'delivered', '2026-04-14 17:44:40'),
(2, 4, 'Михайлова,88', '3000.00', '2026-04-16', '1122334567', 'Марго', '880005553577', 'delivered', '2026-04-14 19:10:21'),
(3, 5, 'Ленина,78а', '11500.00', '2026-05-23', '2200 2288 3667 8889', 'Andy', '+79999999999', 'delivered', '2026-04-14 19:12:42'),
(4, 4, 'Строителей, 76', '4000.00', '2026-04-15', '1234 5678 9123 4567', 'Марго', '89999997899', 'shipped', '2026-04-15 05:30:55');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(2, 2, 13, 1, '3000.00'),
(3, 3, 11, 1, '4500.00'),
(4, 3, 8, 1, '2200.00'),
(5, 3, 4, 1, '4800.00'),
(6, 4, 12, 1, '4000.00');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`) VALUES
(1, 'Букет Пушинка', 'Нежный букет из розовых и красных роз', '2000.00', '1.webp'),
(2, 'Букет Нежность', 'Для самых ласковых розовые розы', '1500.00', '2.webp'),
(3, 'Букет неон', 'Яркие пионы в букете', '6000.00', '3.webp'),
(4, 'Букет Весенний', 'Весенний букет тюльпанов', '4800.00', '4.webp'),
(5, 'Букет белый вальс', 'Осенний букет хризантем', '4500.00', '5.webp'),
(6, 'Букет солнышко', 'Яркий букет из мимоза итальянской', '3600.00', '6.webp'),
(7, 'Букет пастель', 'Нежный букет из голубой гортензии и кустовых роз', '2200.00', '7.webp'),
(8, 'Букет белоснежка', 'Необычный букет из белых лилий', '2200.00', '8.webp'),
(9, 'Букет герда', 'Букет из кустовых роз и эустомы', '5200.00', '9.webp'),
(10, 'Блю', 'Прекрасные синие гвоздики', '2500.00', '10.webp'),
(11, 'Милка', 'Букет пушистых фиолетовых гортензий', '4500.00', '11.webp'),
(12, 'Классика', 'Классический букет красных роз', '4000.00', '12.webp'),
(13, 'Романтик', 'Нежный букет гермини', '3000.00', '13.webp'),
(14, 'Летний', 'Букет из гвоздик, роз и листьев фисташки', '6500.00', '14.webp'),
(15, 'Розовое счастье', 'Нежный букет из гвоздик, гортензий, роз, хризантем, листьев фисташки', '8700.00', '15.webp');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `rating` int NOT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `user_name`, `rating`, `comment`, `status`, `created_at`) VALUES
(3, 5, 'Rosa', 5, 'Мне очень понравились букеты!!!', 'approved', '2026-04-14 19:13:16'),
(5, 8, 'yosanmo', 5, 'красивые и милые букетики!!\r\nмне очень понравились🥰\r\nи сайт удобный, легок в использовании) ', 'approved', '2026-04-14 19:33:07'),
(6, 10, 'Almaz', 5, 'Отличный букет, сделали все быстро и качественно!\r\n', 'approved', '2026-04-14 19:38:31'),
(8, 11, 'Banan', 5, 'Спасибо, очень вручили прекрасным букетом. Вид и свежесть товара на высоте, а главное очень приятные цены, жена очень рада. ', 'approved', '2026-04-14 19:41:18'),
(9, 12, 'Grafff6', 5, 'Красивые букеты, по доступной цене,красивые и яркие. 10/10', 'approved', '2026-04-14 19:41:23'),
(10, 9, 'Асюшка', 5, 'Потрясающий магазин! Букеты выглядят роскошно и стильно, каждая композиция — как произведение искусства. Очень приятно, что флористы слышат пожелания и создают именно то, о чём просишь. Обязательно вернусь сюда ещё', 'approved', '2026-04-14 19:41:52'),
(11, 4, 'Марго', 5, 'Все супер', 'pending', '2026-04-15 03:56:02'),
(12, 4, 'Марго', 2, 'Ддэ', 'pending', '2026-04-15 06:53:12');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `phone`, `email`, `pass`, `role`) VALUES
(1, 'admin', '', 'admin@butoshka.ru', 'admin', 'admin'),
(3, 'devnyash', '+7123456789', 'associalpersonalitydisorder@gmail.com', '123456', 'user'),
(4, 'Марго', '880005553577', 'margo@mail.ru', '12345', 'user'),
(5, 'Rosa', '880005553535', 'rosko@mail.ru', '11111', 'user'),
(6, 'kxkxkxkkx', '891938812', 'mxkdkslzk@gmail.com', 'popapisya', 'user'),
(7, 'popapisya', '8920472728', 'maktraher@gmail.com', 'popapisya', 'user'),
(8, 'yosanmo', '8929392019', 'yosanmo@gmail.com', 'yosanmo', 'user'),
(9, 'Асюшка', '+79301551322', 'ChebanAsika@yandex.ru', '26042006', 'user'),
(10, 'Almaz', '8999999999', 'ipek@mail.ru', '123456', 'user'),
(11, 'Banan', '89512175462', 'mushindima261005@gmail.com', 'D26A10M2005', 'user'),
(12, 'Grafff6', '+79829984545', 'baumakima.com@gmail.com', 'Warface228', 'user');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
