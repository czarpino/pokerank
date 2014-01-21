<?php

/**
 * Using a lookup table containing the 7462 distinct hand values a
 * score is given to a poker hand such that a better hand will always
 * have a higher score.
 * 
 * Cards are represented by 8-bit integers with the following bit
 * scheme:
 * 
 * BIT SCHEME: cdhs|rrrr
 * Where: cdhs = Card suit
 *        rrrr = Card rank
 * EG.
 * 
 * 0x10 => Spade Two
 * 0x20 => Heart Two
 * 0x40 => Diamond Two
 * 0x80 => Club Two
 * ...
 * 0x1C => Spade Ace
 * 0x2C => Heart Ace
 * 0x4C => Diamond Ace
 * 0x8C => Club Ace
 * 
 * Cards are also assign a prime equivalent based on its rank:
 * 
 * (Deuce) 2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41 (Ace)
 * 
 * 
 * @author Czar Pino
 */
class Pokerank
{
    /**
     * Suit values
     */
    const   SPADE   = 1,
            HEART   = 2,
            DIAMOND = 4,
            CLUB    = 8;
    
    /**
     * Rank values
     */
    const   DEUCE   = 0,
            TREY    = 1,
            FOUR    = 2,
            FIVE    = 3,
            SIX     = 4,
            SEVEN   = 5,
            EIGHT   = 6,
            NINE    = 7,
            TEN     = 8,
            JACK    = 9,
            QUEEN   = 10,
            KING    = 11,
            ACE     = 12;
    
    /**
     * The number of distinct values a hand can have
     */
    const TOTAL_DISTINCT = 7462;
    
    /**
     * When added to highest straight value yields 7642 which is the
     * TOTAL_DISTINCT values and the value of the highest straight
     * flush hand "Royal Flush"
     */
    const STRAIGHT_FLUSH_BONUS = 1599;
    
    /**
     * When added to the highest highcard value yields 7140 which is
     * the value of the highest flush hand
     */
    const HIGHCARD_FLUSH_BONUS = 5863;
    
    /**
     * The lookup table
     * 
     * @var array
     */
    private $lookup = NULL;
    
    /**
     * Generate the lookup table
     * 
     * @return array
     */
    public function createLookup()
    {
        // TODO: optimize maybe
        
        // Skip values for straight flush
        $value = self::TOTAL_DISTINCT - 10;
        $lookup = [];
        $prank = $this->getPrimeRankValues();
        
        // Four-of-a-kind
        for ($four = self::ACE; $four >= self::DEUCE; $four --) {
            for ($kicker = self::ACE; $kicker >= self::DEUCE; $kicker --) {
                if ($four !== $kicker) {
                    $pprod = $prank[$four] *
                             $prank[$four] *
                             $prank[$four] *
                             $prank[$four] *
                             $prank[$kicker];
                    $lookup[$pprod] = $value;
                    $value --;
                }
            }
        }
        
        // Fullhouse
        for ($three = self::ACE; $three >= self::DEUCE; $three --) {
            for ($two = self::ACE; $two >= self::DEUCE; $two --) {
                if ($three !== $two) {
                    $pprod = $prank[$three] *
                             $prank[$three] *
                             $prank[$three] *
                             $prank[$two] *
                             $prank[$two];
                    $lookup[$pprod] = $value;
                    $value --;
                }
            }
        }
        
        // Skip values for flush
        $value -= 1277;
        
        // Straight
        for ($high = self::ACE; $high > self::FIVE; $high --) {
            $pprod = $prank[$high] *
                     $prank[$high - 1] *
                     $prank[$high - 2] *
                     $prank[$high - 3] *
                     $prank[$high - 4];
            $lookup[$pprod] = $value;
            $value --;
        }
        
        // Straight special case: 5-4-3-2-A
        $lookup[$prank[5] * $prank[4] * $prank[3] * $prank[2] * $prank[12]] = $value;
        $value --;
        
        // Three-of-a-kind
        for ($three = self::ACE; $three >= self::DEUCE; $three --) {
            for ($kicker1 = self::ACE; $kicker1 > $three; $kicker1 --) {
                for ($kicker2 = $kicker1 - 1; $kicker2 >= self::DEUCE; $kicker2 --) {
                    if ($three !== $kicker2) {
                        $pprod = $prank[$three] *
                                 $prank[$three] *
                                 $prank[$three] *
                                 $prank[$kicker1] *
                                 $prank[$kicker2];

                        $lookup[$pprod] = $value;
                        $value --;
                    }
                }
            }
            for ($kicker1 = $three - 1; $kicker1 > self::DEUCE; $kicker1 --) {
                for ($kicker2 = $kicker1 - 1; $kicker2 >= self::DEUCE; $kicker2 --) {
                    if ($three !== $kicker2) {
                        $pprod = $prank[$three] *
                                 $prank[$three] *
                                 $prank[$three] *
                                 $prank[$kicker1] *
                                 $prank[$kicker2];

                        $lookup[$pprod] = $value;
                        $value --;
                    }
                }
            }
        }
        
        // Two pair
        for ($pair1 = self::ACE; $pair1 >= self::DEUCE; $pair1 --) {
            for ($pair2 = $pair1 - 1; $pair2 >= self::DEUCE; $pair2 --) {
                for ($kicker = self::ACE; $kicker >= self::DEUCE; $kicker --) {
                    if ($pair1 !== $kicker && $pair2 !== $kicker) {
                        $pprod = $prank[$pair1] *
                                 $prank[$pair1] *
                                 $prank[$pair2] *
                                 $prank[$pair2] *
                                 $prank[$kicker];
                        $lookup[$pprod] = $value;
                        $value --;
                    }
                }
            }
        }
        
        // One pair
        for ($p = self::ACE; $p >= self::DEUCE; $p --) {
            for ($k1 = self::ACE; $k1 >= self::DEUCE; $k1 --) {
                if ($p !== $k1) {
                    for ($k2 = $k1 - 1; $k2 >= self::DEUCE; $k2 --) {
                        if ($p !== $k2) {
                            for ($k3 = $k2 - 1; $k3 >= self::DEUCE; $k3 --) {
                                if ($p !== $k3) {
                                    $pprod = $prank[$p] *
                                             $prank[$p] *
                                             $prank[$k1] *
                                             $prank[$k2] *
                                             $prank[$k3];
                                    $lookup[$pprod] = $value;
                                    $value --;
                                }
                            }
                            
                        }
                        
                    }
                }
                
            }
            
        }
        
        // Highcard
        for ($c1 = self::ACE; $c1 > self::SIX; $c1 --) {
            for ($c2 = $c1 - 1; $c2 >= self::FIVE; $c2 --) {
                for ($c3 = $c2 - 1; $c3 >= self::FOUR; $c3 --) {
                    for ($c4 = $c3 - 1; $c4 >= self::TREY; $c4 --) {
                        for ($c5 = 4 === $c1 - $c4 + 1 ? $c4 - 2 : $c4 - 1; $c5 >= self::DEUCE; $c5 --) {
                            if ($c1 === self::ACE && 5 === $c2 && 4 === $c3 && 3 === $c4 && 2 === $c5) {
                                continue;
                            }
                            $pprod = $prank[$c1] *
                                     $prank[$c2] *
                                     $prank[$c3] *
                                     $prank[$c4] *
                                     $prank[$c5];
                            $lookup[$pprod] = $value;
                            $value --;
                        }
                    }
                }
            }
        }
        
        return $lookup;
    }
    
    /**
     * Set the lookup table
     * 
     * @param array $lookup
     * 
     * @return \Pokerank Self
     */
    public function setLookup($lookup)
    {
        $this->lookup = $lookup;
        
        return $this;
    }
    
    /**
     * Retrieve the lookup table. If the lookup table has not been
     * set it will be created. This may slowdown scoring so it is
     * recommended to set the lookup beforehand using lookup.php.
     * 
     * @return array
     */
    public function getLookup()
    {
        if (NULL === $this->lookup) {
            $this->lookup = $this->createLookup();
        }
        
        return $this->lookup;
    }
    
    /**
     * Get prime values of ranks
     * 
     * @return array
     */
    public function getPrimeRankValues()
    {
        return [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41];
    }
    
    /**
     * Assign a score to the set of cards
     * 
     * @param int $card1
     * @param int $card2
     * @param int $card3
     * @param int $card4
     * @param int $card5
     * 
     * @return int [1, 7462]
     */
    public function score($card1, $card2, $card3, $card4, $card5)
    {
        $prank = $this->getPrimeRankValues();
        $pprod = $prank[($card1 & 0x0F)] *
                 $prank[($card2 & 0x0F)] *
                 $prank[($card3 & 0x0F)] *
                 $prank[($card4 & 0x0F)] *
                 $prank[($card5 & 0x0F)];
        
        $lookup = $this->getLookup();
        $rank = $lookup[$pprod];
                
        // Check if hand is a flush
        if ($card1 & $card2 & $card3 & $card4 & $card5 & 0xF0) {
            if (in_array($pprod, [31367009, 14535931, 6678671, 2800733, 1062347, 323323, 85085, 15015, 2310, 205205])) {
                
                // It's a straight flush!
                $rank += self::STRAIGHT_FLUSH_BONUS;
            }
            else {
                
                // It's a regular flush
                $rank += self::HIGHCARD_FLUSH_BONUS;
            }
        }
        
        return $rank;
    }
    
    /**
     * Convert card to corresponding 8 bit integer
     * 
     * @param $suit Card suit: Club(8), Diamond(4), Heart(2), Spade(1)
     * @param $rank Card rank: Deuce(0), Trey(1), ..., King(11), Ace(12)
     * 
     * @return int
     */
    public function toInt($suit, $rank)
    {
        return ($suit << 4) | $rank;
    }
    
}