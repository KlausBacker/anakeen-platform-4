
ALTER TEXT SEARCH CONFIGURATION french
        ALTER MAPPING FOR hword, hword_part, word
        WITH unaccent, french_stem;


ALTER TEXT SEARCH CONFIGURATION simple
        ALTER MAPPING FOR hword, hword_part, word
        WITH unaccent, simple;
