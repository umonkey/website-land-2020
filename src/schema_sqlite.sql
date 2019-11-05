-- Remember!
-- SQLite only really has these data types: INTEGER, REAL, TEXT, BLOB.

CREATE TABLE IF NOT EXISTS `nodes` (
    `id` INTEGER PRIMARY KEY,      -- Unique node id.
    `parent` INTEGER NULL,         -- Parent node id, if any.
    `lb` INTEGER NOT NULL,         -- COMMENT 'Nested set, left boundary.
    `rb` INTEGER NOT NULL,         -- COMMENT 'Nested set, right boundary.
    `type` TEXT NOT NULL,          -- Some value to distinguish nodes, e.g.: wiki, article, user.
    `created` TEXT NOT NULL,       -- Date when the document was created, probably editable.
    `updated` TEXT NOT NULL,       -- Date when the document was last saved, autoupdated.
    `key` TEXT NULL,               -- Additional string key for things like wiki.
    `published` INTEGER NOT NULL,  -- Set to 1 to publish the document.
    `more` BLOB                    -- Additional data, serialize()d.
);
CREATE INDEX IF NOT EXISTS IDX_nodes_parent ON nodes (parent);
CREATE INDEX IF NOT EXISTS IDX_nodes_lb ON nodes (lb);
CREATE INDEX IF NOT EXISTS IDX_nodes_rb ON nodes (rb);
CREATE INDEX IF NOT EXISTS IDX_nodes_type ON nodes (type);
CREATE INDEX IF NOT EXISTS IDX_nodes_created ON nodes (created);
CREATE INDEX IF NOT EXISTS IDX_nodes_key ON nodes (`key`);
CREATE INDEX IF NOT EXISTS IDX_nodes_published ON nodes (published);


CREATE TABLE IF NOT EXISTS `node_rel` (
    `tid` INTEGER,
    `nid` INTEGER
);
CREATE INDEX IF NOT EXISTS IDX_node_rel_nid ON node_rel (nid);
CREATE INDEX IF NOT EXISTS IDX_node_rel_tid ON node_rel (tid);


CREATE TABLE IF NOT EXISTS `cache` (
    `key` TEXT PRIMARY KEY,
    `value` BLOB NOT NULL
);


CREATE TABLE IF NOT EXISTS `accounts` (
    `id` INTEGER PRIMARY KEY,
    `login` TEXT NOT NULL,
    `password` TEXT NULL,
    `last_login` DATETIME NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS IDX_accounts_login ON accounts (login);


CREATE TABLE IF NOT EXISTS `history` (
    `id` INTEGER PRIMARY KEY,
    `node_id` INTEGER NOT NULL,
    `created` INTEGER NOT NULL,
    `data` BLOB NOT NULL
);
CREATE INDEX IF NOT EXISTS IDX_history_node_id ON history (node_id);
CREATE INDEX IF NOT EXISTS IDX_history_created ON history (created);


-- Generic file storage table.  Uploaded files go here.
CREATE TABLE IF NOT EXISTS `files` (
    `id` INTEGER PRIMARY KEY,     -- node id, holds metadata
    `body` BLOB,                  -- file contents
    `hash` TEXT NOT NULL          -- body hash for synchronizing
);
CREATE INDEX IF NOT EXISTS IDX_files_hash ON files (hash);


CREATE TABLE IF NOT EXISTS `sessions` (
    `id` TEXT NOT NULL,
    `updated` TEXT NOT NULL,
    `data` BLOB
);
CREATE UNIQUE INDEX IF NOT EXISTS IDX_sessions_id ON sessions (id);
CREATE INDEX IF NOT EXISTS IDX_sessions_updated ON sessions (updated);


CREATE VIRTUAL TABLE IF NOT EXISTS `search` USING fts5 (`key` UNINDEXED, `meta` UNINDEXED, `title`, `body`);


CREATE TABLE IF NOT EXISTS `odict` (
  `src` TEXT NOT NULL,
  `dst` TEXT NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS IDX_odict_src ON odict (src);


CREATE TABLE IF NOT EXISTS `sales` (
    `id` int,
    `qty` int,
    `date` datetime
);
CREATE INDEX IF NOT EXISTS `IDX_sales_id` ON sales (id);
