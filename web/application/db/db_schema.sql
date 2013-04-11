CREATE TABLE user (
  phone TEXT PRIMARY KEY
  , currentclueid INTEGER REFERENCES clue(id)
);

CREATE TABLE clue (
  id INTEGER PRIMARY KEY
  , name TEXT NOT NULL
  , lat REAL NOT NULL
  , lng REAL NOT NULL
  , clue TEXT NOT NULL
  , hint TEXT
  , question TEXT NOT NULL
  , answer TEXT NOT NULL
  , nextclueid INTEGER REFERENCES clue(id)
);

