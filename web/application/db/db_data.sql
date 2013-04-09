BEGIN;

--clues
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	1
	, 'Brigham Young statue'
	, 40.25050
	, -111.64926
	, 'A grandchild''s rendering,\nWatching over his campus,\nWishing he could sled down the awesome hill in front of him.'
	, 'question'
	, 'answer'
	, 2
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	2
	, 'Bell tower'
	, 40.25275
	, -111.64759
	, 'Because of this, you can hear hymns all over campus.'
	, 'question'
	, 'answer'
	, 3
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	3
	, 'Bowling alley'
	, 40.24836
	, -111.64700
	, 'clue'
	, 'question'
	, 'answer'
	, 4
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	4
	, 'JFSB spiral staircase'
	, 40.24844, -111.65065
	, 'clue'
	, 'question'
	, 'answer'
	, 5
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	5
	, 'Talmage Kung Fu Panda'
	, 40.24935
	, -111.65106
	, 'The likeness of a DreamWorks character, gifted to BYU.'
	, 'question'
	, 'answer'
	, 6
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	6
	, 'Bookstore candy counter'
	, 40.24832
	, -111.64791
	, 'clue'
	, 'question'
	, 'answer'
	, 7
);
INSERT INTO clue (id, name, lat, lng, clue, question, answer, nextclueid) VALUES (
	7
	, 'Testing center'
	, 40.24549
	, -111.65242
	, 'Every semester, many prayers are said here. You may not enter unshaven.'
	, 'question'
	, 'answer'
	, NULL
);

COMMIT;
