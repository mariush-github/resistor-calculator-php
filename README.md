# Resistor Calculator

The script allows you to combine up to four resistors in parallel or in series to obtain a desired value. 

## Usage 

php calculator.php [ optional parameters ] --value [desired value]

### Required parameters:

--value [value] : desired resistor value

### Optional parameters:

--min [value] : minimum resistor value allowed (d=auto,value/100, min=0.01)

--max [value]: maximum resistor value allowed (d=auto,value*100, m=1000000)

--e [value] : use resistors defined in this range, AND previous ranges.

--count [value] : maximum resistors to use (d=3, m=4)

--mix [0,1] : enable groups of resistors in series or parallel (d=1, slower)

--tol [value] : tolerance, max. drift from value allowed +/- n% (d=1, m=10)

--results [value] : maximum results to show on screen (d=25, m=100)

--group [0,1] : group results by number of resistors, from least to most (d=1)

All results are exported to CSV files (Tab separated columns) in the same folder where the script is located. 

### Limitations: 

* E series supported: E3, E6, E12, E24, E48, E96, E192
* The script works with maximum 256 unique resistors, 64 when total resistor value is 4. The calculator will pick the closest 256 unique resistors around the resistor value desired. 
* You can use the --min and --max parameters to reduce the number of unique resistors or you can specify a lower E series.

## Example of usage: 

php calculator.php --min 10 --max 10000 --e 24 --count 3 --results 10 --value 100

will produce the following result: 

```
2        100    0.000% 110 | 1100
2        100    0.000% 82 + 18
2        100    0.000% 150 | 300
2        100    0.000% 200 | 200
3        100    0.000% 110 | 2200 | 2200
3        100    0.000% 120 | 680 | 5100
3        100    0.000% 120 | 750 | 3000
3        100    0.000% 120 | 1000 | 1500
3        100    0.000% 120 | 1200 | 1200
3        100    0.000% 75 + 15 + 10
```