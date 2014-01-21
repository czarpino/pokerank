Pokerank
========

Pokerank is a poker hand scoring algorithm written in PHP. The algorithm assigns a score to a poker hand such that a better hand has a higher score (eg. King-High Straight Flush > King-High Straight).The algorithm is loosely built upon the work of [Cactus Kev](http://www.suffecool.net/poker/evaluator.html) and uses a lookup table containing the 7462 distinct hand values that a hand may take.



Quick Usage
-----------

    $pokerank = new Pokerank();
    $pokerank->setLookup(require ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "lookup.php");

    // Royal flush
    $card1 = $pokerank->toInt(Pokerank::SPADE, Pokerank::ACE);
    $card2 = $pokerank->toInt(Pokerank::SPADE, Pokerank::KING);
    $card3 = $pokerank->toInt(Pokerank::SPADE, Pokerank::QUEEN);
    $card4 = $pokerank->toInt(Pokerank::SPADE, Pokerank::JACK);
    $card5 = $pokerank->toInt(Pokerank::SPADE, Pokerank::TEN);

    echo "Score of Royal Flush: ", $pokerank->score($card1, $card2, $card3, $card4, $card5);
    echo "\n";

This is a sample usage of the algorithm. See `sample/sample.php` for more. 



Requirements
------------

The program was developed using PHP5.4 and used some features (eg. `[]` vs `array ()`) not available to 5.3 and below. It shouldn't be too hard to port though.


