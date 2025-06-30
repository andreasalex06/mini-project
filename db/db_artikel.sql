CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `ringkasan` text DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `category_enum` enum('teknologi','pendidikan','bisnis','kesehatan','sains','lifestyle','olahraga','hiburan','umum') DEFAULT 'umum',
  `user_id` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `views` int(11) DEFAULT 0,
  `created_at` DATETIME NOT NULL, -- DEFAULT DIHAPUS
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artikel`
--

INSERT INTO `artikel` (`id`, `judul`, `konten`, `ringkasan`, `kategori_id`, `category_enum`, `user_id`, `gambar`, `status`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Selamat Datang di Literaturku', 'Literaturku adalah platform untuk berbagi pengetahuan dan literasi. Di sini Anda dapat membaca berbagai artikel menarik dari berbagai kategori seperti teknologi, pendidikan, bisnis, kesehatan, dan sains. Mari bersama-sama membangun komunitas yang gemar membaca dan berbagi pengetahuan!', 'Platform untuk berbagi pengetahuan dan literasi dengan berbagai kategori artikel menarik.', 2, 'umum', 1, NULL, 'published', 4, '2025-06-29 14:03:40', '2025-06-30 13:26:24'),
(2, 'Pentingnya Literasi Digital di Era Modern', 'Literasi digital menjadi sangat penting di era modern ini. Dengan kemajuan teknologi yang pesat, setiap orang perlu memiliki kemampuan untuk menggunakan teknologi digital dengan bijak dan efektif. Literasi digital tidak hanya tentang cara menggunakan komputer atau smartphone, tetapi juga tentang memahami informasi digital, keamanan online, dan etika digital.', 'Pentingnya memahami dan menguasai literasi digital di era teknologi modern.', 1, 'umum', 1, NULL, 'published', 7, '2025-06-29 14:03:40', '2025-06-29 16:54:05'),
(4, 'Shofiaku', 'skasjakkwoajssnsmaw saassnmemeoqoadppfoeolsdkdlkw Paodofsdkalxkaksjdsjsjsjjjjjjjjjjjjjjjjssssssssssssssssjjjjjjjjjjjjjjjjjjssssssssssssssssjjjjj', 'sofia cantik jelita nan seksoy', NULL, 'hiburan', 4, NULL, 'published', 43, '2025-06-29 17:12:47', '2025-06-30 15:06:39'),
(5, 'Penguasa Oh Penguasa', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\r\n\r\nNunc pulvinar sapien et ligula ullamcorper, sit amet vestibulum erat ultrices. Proin vel nunc nec magna lacinia cursus. Fusce laoreet sapien in justo efficitur, ut consequat magna pharetra. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus et tortor sed quam finibus faucibus. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi.\r\n\r\nMaecenas tincidunt, augue et rutrum condimentum, libero lectus mattis magna, ut blandit eros justo vel erat. Nam varius, eros sed facilisis finibus, purus magna tincidunt sapien, vel efficitur eros ex a quam. In hac habitasse platea dictumst. Duis non enim et orci finibus mattis. Sed vel enim a odio gravida sodales. Curabitur elementum, est a condimentum rhoncus, sem magna vehicula neque, a faucibus turpis eros ut quam.\r\n\r\nPhasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem.\r\n\r\nDonec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', NULL, 'pendidikan', 5, NULL, 'published', 0, '2025-06-30 13:42:38', '2025-06-30 13:42:38'),
(6, 'sains adalah kunci', 'sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq', NULL, 'bisnis', 5, NULL, 'published', 2, '2025-06-30 14:04:15', '2025-06-30 14:35:08'),
(7, 'poopp', 'tuuuuuuuuuuuuuuuuuuuuuuuuuuuuy gggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk', 'wewwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww', NULL, 'kesehatan', 5, NULL, 'published', 0, '2025-06-30 14:38:55', '2025-06-30 14:38:55'),
(8, 'lani namuu', 'kungggggggggggggggggggggggggggggg kokiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii laaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'poiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii hoooooooooooooooooooooooooooooooooooo', NULL, 'lifestyle', 5, NULL, 'published', 2, '2025-06-30 14:42:32', '2025-06-30 14:43:12');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `warna` varchar(7) DEFAULT '#10367d',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`, `warna`, `created_at`) VALUES
(1, 'Teknologi', 'Artikel tentang teknologi terbaru', '#667eea', '2025-06-29 14:03:40'),
(2, 'Pendidikan', 'Artikel seputar dunia pendidikan', '#2ecc71', '2025-06-29 14:03:40'),
(3, 'Bisnis', 'Artikel tentang dunia bisnis dan ekonomi', '#e74c3c', '2025-06-29 14:03:40'),
(4, 'Kesehatan', 'Artikel tentang kesehatan dan gaya hidup', '#f39c12', '2025-06-29 14:03:40'),
(5, 'Sains', 'Artikel ilmu pengetahuan', '#9b59b6', '2025-06-29 14:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@artikel.com', '$2y$10$x835NBgY9P2A5H1NzrqkZu/FuClBVWmVYAWJveVNB5xjNtaBdzHj6', '2025-06-29 12:34:36'),
(2, 'andreasalexyz', 'andreasalexyz@gmail.com', '$2y$10$JVEgnJqIoisdvawU995bLuQcCGGxOXhNm7PVfxwP7ueYVvAclsFPS', '2025-06-29 12:39:55'),
(3, 'andreasalexyz@gmail.com', 'shofi@gmail.com', '$2y$10$0Eqa5O8gl1HM9tqCPI5jQe8AzsnBlHVjBR0x7e4L3hXI5b5bdofla', '2025-06-29 13:03:27'),
(4, 'sofia', 'shofia@gmail.com', '$2y$10$v31nrDfSEnM.X0t94Dz85.1d7Ch4Yd0nh.WGCw4VG0DwujmP7Kuvq', '2025-06-29 16:40:49'),
(5, 'ian sopian', 'ian@gmail.com', '$2y$10$4ZVU2lutsscHkeeTEUIn0uGCBJcXUFLTGBszWZpfwSTDW3DYFTNZW', '2025-06-30 13:35:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
