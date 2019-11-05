CREATE TABLE IF NOT EXISTS `sessions` (
    `id` CHAR(32) NOT NULL,
    `updated` DATETIME NOT NULL,
    `data` MEDIUMBLOB NOT NULL,
    PRIMARY KEY(`id`),
    KEY(`updated`)
);

CREATE TABLE IF NOT EXISTS `cache` (
    `key` CHAR(32) NOT NULL,
    `added` INTEGER NOT NULL,
    `value` BLOB,
    PRIMARY KEY(`key`)
);


CREATE TABLE IF NOT EXISTS `nodes` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,  -- Unique node id.
    `parent` INTEGER NULL,                          -- Parent node id, if any.
    `lb` INTEGER NOT NULL,                          -- COMMENT 'Nested set, left boundary.
    `rb` INTEGER NOT NULL,                          -- COMMENT 'Nested set, right boundary.
    `type` VARCHAR(32) NOT NULL,                    -- Some value to distinguish nodes, e.g.: wiki, article, user.
    `created` DATETIME NOT NULL,                    -- Date when the document was created, probably editable.
    `updated` DATETIME NOT NULL,                    -- Date when the document was last saved, autoupdated.
    `key` VARCHAR(255) NULL,                        -- Additional string key for things like wiki.
    `published` TINYINT(1) UNSIGNED NOT NULL,       -- Set to 1 to publish the document.
    `more` MEDIUMBLOB,                              -- Additional data, serialize()d.
    PRIMARY KEY(`id`),
    KEY(`parent`),
    KEY(`lb`),
    KEY(`rb`),
    KEY(`type`),
    KEY(`created`),
    KEY(`updated`),
    KEY(`published`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `nodes_user_idx` (
    `id` INTEGER UNSIGNED NOT NULL,
    `email` VARCHAR(255) NULL,
    FOREIGN KEY (`id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE,
    KEY(`email`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `nodes_picture_idx` (
    `id` INTEGER UNSIGNED NOT NULL,
    `author` integer unsigned not NULL,
    FOREIGN KEY (`id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE,
    KEY(`author`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `search` (
    `key` VARCHAR(255) NOT NULL,
    `meta` MEDIUMBLOB NULL,
    `title` MEDIUMTEXT NULL,
    `body` MEDIUMTEXT NULL,
    PRIMARY KEY(`key`),
    FULLTEXT KEY(`title`),
    FULLTEXT KEY(`body`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `odict` (
    `src` VARCHAR(255) NOT NULL,
    `dst` VARCHAR(255) NOT NULL,
    PRIMARY KEY(`src`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `taskq` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `priority` INTEGER NOT NULL DEFAULT 0,
  `payload` MEDIUMBLOB NOT NULL COMMENT 'serialized data',
  PRIMARY KEY(`id`),
  KEY(`priority`)
) DEFAULT CHARSET utf8;


CREATE TABLE IF NOT EXISTS `sales` (
    `id` integer unsigned not null,
    `qty` integer unsigned not null,
    `date` datetime not null,
    KEY(`id`),
    KEY(`date`)
);


CREATE TABLE IF NOT EXISTS `categories` (
    `id` integer unsigned not null,
    `parent` integer unsigned null,
    `name` varchar(255) not null,
    primary key(`id`),
    key(`parent`),
    key(`name`)
) DEFAULT CHARSET utf8;
