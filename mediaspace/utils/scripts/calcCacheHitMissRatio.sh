#!/bin/bash

HIT=`grep "cache: hit on" $1 |  wc | awk '{ print $1 }'`;
MISS=`grep "cache: missed on" $1 | wc | awk '{ print $1 }'`

echo "Cache  Hit: $HIT";
echo "Cache Miss: $MISS";
RATIO=`echo "scale=4; $MISS / $HIT" | bc`;
echo "     Ratio: $RATIO";
