
   INFO  Running migrations.  

  0001_01_01_000000_create_roles_table ...............................................................................  
  ⇂ create table `roles` (`id` bigint unsigned not null auto_increment primary key, `description` varchar(255) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `roles` add unique `roles_description_unique`(`description`)  
  0001_01_01_000001_create_customers_table ...........................................................................  
  ⇂ create table `customers` (`NIT` int not null, `name` varchar(255) not null, `email` varchar(255) not null, `phone` varchar(255) null, `cellphone` varchar(255) null, `address` varchar(255) null, `department` varchar(255) null, `active` tinyint(1) not null default '1', `role_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`NIT`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `customers` add constraint `customers_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade  
  ⇂ alter table `customers` add unique `customers_email_unique`(`email`)  
  0001_01_01_000002_create_users_table ...............................................................................  
  ⇂ create table `users` (`id` bigint unsigned not null auto_increment primary key, `username` varchar(255) not null, `name` varchar(255) not null, `surname` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `phone` varchar(255) null, `password` varchar(255) not null, `active` tinyint(1) not null default '1', `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null, `role_id` bigint unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `users` add constraint `users_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade  
  ⇂ alter table `users` add unique `users_username_unique`(`username`)  
  ⇂ alter table `users` add unique `users_email_unique`(`email`)  
  ⇂ create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `sessions` add index `sessions_user_id_index`(`user_id`)  
  ⇂ alter table `sessions` add index `sessions_last_activity_index`(`last_activity`)  
  0001_01_01_000003_create_cache_table ...............................................................................  
  ⇂ create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  0001_01_01_000004_create_jobs_table ................................................................................  
  ⇂ create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `jobs` add index `jobs_queue_index`(`queue`)  
  ⇂ create table `job_batches` (`id` varchar(255) not null, `name` varchar(255) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`)  
  2025_03_31_152909_create_audits_table ..............................................................................  
  ⇂ create table `audits` (`id` bigint unsigned not null auto_increment primary key, `auditable_type` varchar(255) not null, `auditable_id` bigint unsigned not null, `action` varchar(255) not null, `user_id` bigint unsigned null, `old_values` text null, `new_values` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `audits` add index `audits_auditable_type_auditable_id_index`(`auditable_type`, `auditable_id`)  
  ⇂ alter table `audits` add constraint `audits_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null  
  2025_03_31_233303_add_force_password_change_to_users_table .........................................................  
  ⇂ alter table `users` add `force_password_change` tinyint(1) not null default '1' after `password`  
  2025_04_01_201140_create_continents_table ..........................................................................  
  ⇂ create table `continents` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `code` varchar(3) not null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `continents` add unique `continents_name_unique`(`name`)  
  ⇂ alter table `continents` add unique `continents_code_unique`(`code`)  
  2025_04_01_201157_create_countries_table ...........................................................................  
  ⇂ create table `countries` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `code` varchar(3) not null, `continent_id` bigint unsigned not null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `countries` add constraint `countries_continent_id_foreign` foreign key (`continent_id`) references `continents` (`id`) on delete cascade  
  ⇂ alter table `countries` add unique `countries_name_unique`(`name`)  
  ⇂ alter table `countries` add unique `countries_code_unique`(`code`)  
  2025_04_01_201204_create_cities_table ..............................................................................  
  ⇂ create table `cities` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `country_id` bigint unsigned not null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `cities` add constraint `cities_country_id_foreign` foreign key (`country_id`) references `countries` (`id`) on delete cascade  
  2025_04_02_202006_create_quantity_descriptions_table ...............................................................  
  ⇂ create table `quantity_descriptions` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  2025_04_02_203400_create_incoterms_table ...........................................................................  
  ⇂ create table `incoterms` (`id` bigint unsigned not null auto_increment primary key, `code` varchar(3) not null, `name` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `incoterms` add unique `incoterms_code_unique`(`code`)  
  2025_04_02_221848_create_services_table ............................................................................  
  ⇂ create table `services` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  2025_04_02_222843_create_costs_table ...............................................................................  
  ⇂ create table `costs` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  2025_04_03_022106_create_quotations_table ..........................................................................  
  ⇂ create table `quotations` (`id` bigint unsigned not null auto_increment primary key, `delivery_date` datetime not null, `reference_number` varchar(255) not null, `reference_customer` varchar(255) null, `currency` varchar(255) not null, `exchange_rate` decimal(10, 2) not null, `amount` decimal(15, 2) not null, `status` varchar(255) not null default 'pending', `observations` text null, `users_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null, `customer_nit` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `quotations` add constraint `quotations_users_id_foreign` foreign key (`users_id`) references `users` (`id`) on delete cascade  
  ⇂ alter table `quotations` add constraint `quotations_customer_nit_foreign` foreign key (`customer_nit`) references `customers` (`NIT`) on delete cascade  
  2025_04_03_150451_create_products_table ............................................................................  
  ⇂ create table `products` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) null, `quotation_id` bigint unsigned not null, `origin_id` bigint unsigned not null, `destination_id` bigint unsigned not null, `incoterm_id` bigint unsigned not null, `quantity_description_id` bigint unsigned not null, `quantity` varchar(20) not null, `weight` decimal(10, 2) not null, `volume` decimal(10, 2) not null, `volume_unit` enum('kg_vol', 'm3') not null, `description` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `products` add constraint `products_quotation_id_foreign` foreign key (`quotation_id`) references `quotations` (`id`) on delete cascade  
  ⇂ alter table `products` add constraint `products_origin_id_foreign` foreign key (`origin_id`) references `cities` (`id`)  
  ⇂ alter table `products` add constraint `products_destination_id_foreign` foreign key (`destination_id`) references `cities` (`id`)  
  ⇂ alter table `products` add constraint `products_incoterm_id_foreign` foreign key (`incoterm_id`) references `incoterms` (`id`)  
  ⇂ alter table `products` add constraint `products_quantity_description_id_foreign` foreign key (`quantity_description_id`) references `quantity_descriptions` (`id`)  
  2025_04_03_150951_create_cost_details_table ........................................................................  
  ⇂ create table `cost_details` (`id` bigint unsigned not null auto_increment primary key, `quotation_id` bigint unsigned not null, `cost_id` bigint unsigned not null, `concept` varchar(255) not null, `amount` decimal(10, 2) not null, `currency` varchar(255) not null default 'USD', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `cost_details` add constraint `cost_details_quotation_id_foreign` foreign key (`quotation_id`) references `quotations` (`id`) on delete cascade  
  ⇂ alter table `cost_details` add constraint `cost_details_cost_id_foreign` foreign key (`cost_id`) references `costs` (`id`) on delete cascade  
  2025_04_03_151506_create_quotation_services_table ..................................................................  
  ⇂ create table `quotation_services` (`id` bigint unsigned not null auto_increment primary key, `quotation_id` bigint unsigned not null, `service_id` bigint unsigned not null, `included` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `quotation_services` add constraint `quotation_services_quotation_id_foreign` foreign key (`quotation_id`) references `quotations` (`id`) on delete cascade  
  ⇂ alter table `quotation_services` add constraint `quotation_services_service_id_foreign` foreign key (`service_id`) references `services` (`id`)  
  2025_04_03_151811_create_exchange_rates_table ......................................................................  
  ⇂ create table `exchange_rates` (`id` bigint unsigned not null auto_increment primary key, `source_currency` varchar(10) not null, `target_currency` varchar(10) not null, `rate` decimal(15, 8) not null, `date` date not null, `active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `exchange_rates` add unique `exchange_rates_source_currency_target_currency_unique`(`source_currency`, `target_currency`)  
  2025_04_08_233350_create_billing_notes_table .......................................................................  
  ⇂ create table `billing_notes` (`id` bigint unsigned not null auto_increment primary key, `op_number` varchar(255) not null comment 'Formato OP-001-25', `note_number` varchar(255) not null comment 'Formato No-001-25', `emission_date` date not null, `total_amount` decimal(12, 2) not null, `currency` varchar(3) not null, `exchange_rate` decimal(10, 4) not null, `user_id` bigint unsigned not null, `quotation_id` bigint unsigned not null, `customer_nit` int not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `billing_notes` add constraint `billing_notes_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade  
  ⇂ alter table `billing_notes` add constraint `billing_notes_quotation_id_foreign` foreign key (`quotation_id`) references `quotations` (`id`) on delete cascade  
  ⇂ alter table `billing_notes` add constraint `billing_notes_customer_nit_foreign` foreign key (`customer_nit`) references `customers` (`NIT`) on delete cascade  
  ⇂ alter table `billing_notes` add unique `billing_notes_op_number_unique`(`op_number`)  
  ⇂ alter table `billing_notes` add unique `billing_notes_note_number_unique`(`note_number`)  
  2025_04_08_233654_create_billing_note_items_table ..................................................................  
  ⇂ create table `billing_note_items` (`id` bigint unsigned not null auto_increment primary key, `billing_note_id` bigint unsigned not null, `cost_id` bigint unsigned not null, `description` varchar(255) not null, `amount` decimal(12, 2) not null, `currency` varchar(3) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `billing_note_items` add constraint `billing_note_items_billing_note_id_foreign` foreign key (`billing_note_id`) references `billing_notes` (`id`) on delete cascade  
  ⇂ alter table `billing_note_items` add constraint `billing_note_items_cost_id_foreign` foreign key (`cost_id`) references `costs` (`id`) on delete restrict  
  ⇂ alter table `billing_note_items` add index `billing_note_items_billing_note_id_index`(`billing_note_id`)  

