INSERT INTO `@prefixuser` (`user_id`, `f_name`, `email`, `md5_email`, `md5_password`, `group_id`, `active`, `system`, `confirmed`)
VALUES ("2", "Generci admin", 'admin@admin.com', '64e1b8d34f425d19e1ee2ea7236d3028', '64e1b8d34f425d19e1ee2ea7236d3028', 1, 1, 0, 1) ON DUPLICATE KEY UPDATE Active = 1;