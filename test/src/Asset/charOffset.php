This file used to test the conversion from file based character offset to line based character offset. Thus calculate
the position of a character on a line when the position within the file is given.

test:

The following characters XXXXXX are known to start at position 233 within the file. The start position within line
should be 25 (indexed zero based). These characters YYYYY start at 375, which should resolve to 52.

And finally a test where the given is 494 and the result must be zero
ZZZZZ

One more test XXXXX at 515 and 14