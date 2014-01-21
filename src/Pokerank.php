<?php

/**
 * Using a lookup table containing the 7462 distinct hand values a
 * score is given to a poker hand such that a better hand will always
 * have a higher score.
 * 
 * 
 * Cards are assigned a prime equivalent based on its rank:
 * 
 * (Deuce) 2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41 (Ace)
 * 
 * 
 * A Cards is represented by a 12-bit integer with the following bit
 * scheme:
 * 
 * BIT SCHEME: ssss | pppp pppp
 * ssss => Card suit (spade=1=0b0001, Hear=2=0b0010, Diamond=4=0b0100, Club=8=0b1000)
 * pppppppp => Card prime value (Deuce=2=0b00000010, ..., Ace=41=0b00101001)
 * 
 * EG.
 * 
 * Hex   |   Card
 * --------------------
 * 
 * 0x102 => 2 of Spades
 * 0x103 => 3 of Spades
 * 0x105 => 4 of Spades
 * ...
 * 0x129 => Ace of Spades
 * 
 * 0x202 => 2 of Heart
 * 0x203=> 3 of Heart
 * 0x205 => 4 of Heart
 * ...
 * 0x229 => Ace of Heart
 * ...
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
     * prime values of card ranks
     * 
     * @var array
     */
    private $primeRankValues = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41];
    
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
        
        // Four-of-a-kind
        for ($four = self::ACE; $four >= self::DEUCE; $four --) {
            for ($kicker = self::ACE; $kicker >= self::DEUCE; $kicker --) {
                if ($four !== $kicker) {
                    $pprod = $this->primeRankValues[$four] *
                             $this->primeRankValues[$four] *
                             $this->primeRankValues[$four] *
                             $this->primeRankValues[$four] *
                             $this->primeRankValues[$kicker];
                    $lookup[$pprod] = $value;
                    $value --;
                }
            }
        }
        
        // Fullhouse
        for ($three = self::ACE; $three >= self::DEUCE; $three --) {
            for ($two = self::ACE; $two >= self::DEUCE; $two --) {
                if ($three !== $two) {
                    $pprod = $this->primeRankValues[$three] *
                             $this->primeRankValues[$three] *
                             $this->primeRankValues[$three] *
                             $this->primeRankValues[$two] *
                             $this->primeRankValues[$two];
                    $lookup[$pprod] = $value;
                    $value --;
                }
            }
        }
        
        // Skip values for flush
        $value -= 1277;
        
        // Straight
        for ($high = self::ACE; $high > self::FIVE; $high --) {
            $pprod = $this->primeRankValues[$high] *
                     $this->primeRankValues[$high - 1] *
                     $this->primeRankValues[$high - 2] *
                     $this->primeRankValues[$high - 3] *
                     $this->primeRankValues[$high - 4];
            $lookup[$pprod] = $value;
            $value --;
        }
        
        // Straight special case: 5-4-3-2-A
        $lookup[$this->primeRankValues[5] * $this->primeRankValues[4] * $this->primeRankValues[3] * $this->primeRankValues[2] * $this->primeRankValues[12]] = $value;
        $value --;
        
        // Three-of-a-kind
        for ($three = self::ACE; $three >= self::DEUCE; $three --) {
            for ($kicker1 = self::ACE; $kicker1 > $three; $kicker1 --) {
                for ($kicker2 = $kicker1 - 1; $kicker2 >= self::DEUCE; $kicker2 --) {
                    if ($three !== $kicker2) {
                        $pprod = $this->primeRankValues[$three] *
                                 $this->primeRankValues[$three] *
                                 $this->primeRankValues[$three] *
                                 $this->primeRankValues[$kicker1] *
                                 $this->primeRankValues[$kicker2];

                        $lookup[$pprod] = $value;
                        $value --;
                    }
                }
            }
            for ($kicker1 = $three - 1; $kicker1 > self::DEUCE; $kicker1 --) {
                for ($kicker2 = $kicker1 - 1; $kicker2 >= self::DEUCE; $kicker2 --) {
                    if ($three !== $kicker2) {
                        $pprod = $this->primeRankValues[$three] *
                                 $this->primeRankValues[$three] *
                                 $this->primeRankValues[$three] *
                                 $this->primeRankValues[$kicker1] *
                                 $this->primeRankValues[$kicker2];

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
                        $pprod = $this->primeRankValues[$pair1] *
                                 $this->primeRankValues[$pair1] *
                                 $this->primeRankValues[$pair2] *
                                 $this->primeRankValues[$pair2] *
                                 $this->primeRankValues[$kicker];
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
                                    $pprod = $this->primeRankValues[$p] *
                                             $this->primeRankValues[$p] *
                                             $this->primeRankValues[$k1] *
                                             $this->primeRankValues[$k2] *
                                             $this->primeRankValues[$k3];
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
                            $pprod = $this->primeRankValues[$c1] *
                                     $this->primeRankValues[$c2] *
                                     $this->primeRankValues[$c3] *
                                     $this->primeRankValues[$c4] *
                                     $this->primeRankValues[$c5];
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
        $pprod = ($card1 & 0x0FF) * ($card2 & 0x0FF) * ($card3 & 0x0FF) *
                 ($card4 & 0x0FF) * ($card5 & 0x0FF);
                
        // Check if hand is a flush
        if (0xF00 & $card1 & $card2 & $card3 & $card4 & $card5) {
            if ($pprod === 31367009 || $pprod === 14535931 ||
                $pprod === 6678671  || $pprod === 2800733  ||
                $pprod === 1062347  || $pprod === 323323   ||
                $pprod === 85085    || $pprod === 15015    ||
                $pprod === 2310     || $pprod === 205205) {
                
                // It's a straight flush!
                return $this->lookup[$pprod] + self::STRAIGHT_FLUSH_BONUS;
            }
            
            // It's a regular flush
            return $this->lookup[$pprod] + self::HIGHCARD_FLUSH_BONUS;
        }
        
        return $this->lookup[$pprod];
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
        return $suit << 8 | $this->primeRankValues[$rank];
    }
    
}