START TRANSACTION;

CREATE TABLE `ale_articles` (
  `id` int(11) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `site` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `first_scrape` datetime NOT NULL,
  `last_scrape` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ale_revisions` (
  `id` int(11) NOT NULL,
  `id_article` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `scrape_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ale_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`);

ALTER TABLE `ale_revisions`
  ADD PRIMARY KEY (`id`);

COMMIT;
