<?php

require ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Pokerank.php";

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

// King-high straight flush
$card1 = $pokerank->toInt(Pokerank::SPADE, Pokerank::KING);
$card2 = $pokerank->toInt(Pokerank::SPADE, Pokerank::QUEEN);
$card3 = $pokerank->toInt(Pokerank::SPADE, Pokerank::JACK);
$card4 = $pokerank->toInt(Pokerank::SPADE, Pokerank::TEN);
$card5 = $pokerank->toInt(Pokerank::SPADE, Pokerank::NINE);
echo "Score of King-High Straight Flush: ", $pokerank->score($card1, $card2, $card3, $card4, $card5);
echo "\n";

// Four-of-a-kind w/ King kicker
$card1 = $pokerank->toInt(Pokerank::SPADE, Pokerank::ACE);
$card2 = $pokerank->toInt(Pokerank::HEART, Pokerank::ACE);
$card3 = $pokerank->toInt(Pokerank::DIAMOND, Pokerank::ACE);
$card4 = $pokerank->toInt(Pokerank::CLUB, Pokerank::ACE);
$card5 = $pokerank->toInt(Pokerank::SPADE, Pokerank::KING);
echo "Four-of-a-kind w/ King: ", $pokerank->score($card1, $card2, $card3, $card4, $card5);
echo "\n";

// TODO: add more samples maybe