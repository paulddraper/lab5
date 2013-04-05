PRAGMA foreign_keys = ON;

CREATE TABLE user (
	, username TEXT PRIMARY KEY
	, currentclueid INTEGER REFERENCES clue(id)
);
s
CREATE TABLE clue (
	id INTEGER PRIMARY KEY
	, lat REAL NOT NULL
	, lng REAL NOT NULL
	, clue TEXT NOT NULL
	, question TEXT NOT NULL
	, answer TEXT NOT NULL
	, nextclueid INTEGER REFERENCES clue(id)
);