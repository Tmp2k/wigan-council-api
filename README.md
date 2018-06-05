# Wigan Council API

This script scrapes the data from Wigan Council's website and returns it in JSON format, for use as a web service.

To get the data you must enter a Unique Property Reference Number (UPRN). You can get a list of UPRNs by first entering a postcode. 
As the service is stateless, you must pass the original postcode along with the UPRN in the second call. 

## Examples

#### GET /all?postcode=WN1+2RW
Returns a JSON formatted list of UPRN => Address

#### GET /all?postcode=WN1+2RW&uprn=UPRN100011822616
Returns a JSON object containing information about that property.

#### GET /bins?postcode=WN1+2RW
Returns a JSON object containing bin collection information for that postcode.

## Acknowledgements
- 256cats' ASPBrowser - https://github.com/256cats/ASPBrowser 
- PHP Simple HTML DOM Parser -  http://sourceforge.net/projects/simplehtmldom/