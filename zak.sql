---////////////////////////////////////////////////////////////////////////////
---// zak.sqlite DB structure
---////////////////////////////////////////////////////////////////////////////

--
-- Использованная кодировка текста: windows-1251
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- Таблица: parameters
CREATE TABLE parameters (

	id    INTEGER      PRIMARY KEY AUTOINCREMENT NOT NULL,

	days_i    INTEGER (2) NOT NULL DEFAULT 5,

	days_z    INTEGER (2) NOT NULL DEFAULT 7,

	in_redactions BOOL DEFAULT true,

	in_history BOOL DEFAULT true,

	in_hrefs BOOL DEFAULT true,

	CONSTRAINT sqlite_autoindex_parameters_1);

-- Таблица: redactions
CREATE TABLE redactions (

	id    INTEGER      PRIMARY KEY AUTOINCREMENT NOT NULL,

	code  VARCHAR (16) NOT NULL,

	uid   VARCHAR (16),

	zdate DATE,

	title VARCHAR (50),

	zcode VARCHAR (20),

	zfrom DATE,

	CONSTRAINT sqlite_autoindex_redactions_1);

-- Таблица: docs_attributes
CREATE TABLE docs_attributes (

	code        VARCHAR (16) PRIMARY KEY NOT NULL,

	vers        INT,

	updtdate    DATE,

	codeLG      VARCHAR,

	uid         VARCHAR,

	title       VARCHAR,

	gosnum      VARCHAR (20),

	status      VARCHAR,

	numbers     VARCHAR(20),

	doc_date    DATE,

	regnum      VARCHAR(20),

	regdate     DATE,

	vidy        VARCHAR (3),

	publish     VARCHAR (3),

	npa         VARCHAR,

	modify_date DATETIME DEFAULT (CURRENT_TIMESTAMP),

	CONSTRAINT sqlite_autoindex_docs_attributes_3);

-- Таблица: publish
CREATE TABLE publish (

	publish  VARCHAR (3) NOT NULL PRIMARY KEY ON CONFLICT IGNORE,

	title    VARCHAR (200),

	CONSTRAINT sqlite_autoindex_publish_1);

-- Таблица: vidy
CREATE TABLE vidy (

	vidy     INTEGER (3) NOT NULL PRIMARY KEY ON CONFLICT IGNORE,

	title    VARCHAR (255),

	CONSTRAINT sqlite_autoindex_vidy_1);

-- Таблица: history
CREATE TABLE history (

	id    INTEGER      PRIMARY KEY AUTOINCREMENT NOT NULL,

	code      VARCHAR (16) NOT NULL,

	his_date  DATE,

	his_title VARCHAR (50),

	his_code  VARCHAR (20),

	CONSTRAINT sqlite_autoindex_history_1);

-- Таблица: users
CREATE TABLE users (

  	users_id INTEGER PRIMARY KEY AUTOINCREMENT  NOT NULL,

 	users_login VARCHAR(30) NOT NULL,

 	users_password VARCHAR(32) NOT NULL,

  	users_hash VARCHAR(32) NOT NULL);

-- Таблица: hrefs
CREATE TABLE hrefs (

	id    INTEGER      PRIMARY KEY AUTOINCREMENT NOT NULL,

	code       VARCHAR (16) NOT NULL,

	hrefs_code VARCHAR (16),

	CONSTRAINT sqlite_autoindex_hrefs_1);

-- Таблица: hand_zminy
CREATE TABLE hand_zminy (

	file    VARCHAR (16), 

	updtdate DATE,	

	code	VARCHAR (16) NOT NULL,

	zcode	VARCHAR (16) NOT NULL,

	mark	VARCHAR (1),

	checked VARCHAR (1),

	modify_date DATETIME DEFAULT (CURRENT_TIMESTAMP),

	CONSTRAINT sqlite_autoindex_hand_zminy_1);

-- Индекс: r_code
CREATE INDEX r_code ON redactions (code, zcode);

-- Индекс: h_code
CREATE INDEX h_code ON hrefs (code);

-- Индекс: hi_code
CREATE INDEX hi_code ON history (code);

-- Индекс: 
CREATE INDEX "" ON docs_attributes (vers);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;

