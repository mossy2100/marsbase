/**
 * numbers.js
 * ==========
 * A variety of handy functions for working with numbers.
 *
 * @author		Shaun Moss
 * @lastUpdate	2008-09-05
 * @requires	strings.js
 * @requires	php-strings.js
 */

// commonly used in time/date displays:
// pad numerical string with leading zeroes until 2 chars total:
function twoDigits(n)
{
	return padLeft(n, 2, "0");
}


// pad numerical string with leading zeroes until 3 chars total:
function threeDigits(n)
{
	return padLeft(n, 3, "0");
}


// pad numerical string with leading zeroes until 4 chars total:
function fourDigits(n)
{
	return padLeft(n, 4, "0");
}


function sign(num, plusSign)
{
	return num < 0 ? '-' : (plusSign ? '+' : '');
}


function strtonum(str)
{
	// removes the commas inserted by a numberFormat or a user, and
	// converts to a floating point number:
	str = str_replace(",", "", str);
	// convert to float:
	var num = parseFloat(str);
	// if not a number then return 0:
	return isNaN(num) ? 0 : num;
}


function strtoint(str)
{
	// removes the commas inserted by a numberFormat or a user, and
	// converts to an integer:
	str = str_replace(",", "", str);
	// convert to integer:
	var num = parseInt(str, 10);
	// if not a number then return 0:
	return isNaN(num) ? 0 : num;
}


function strtonat(str)
{
	// removes the commas inserted by a numberFormat or a user, and
	// converts to a natural number (non-negative integer):
	str = str_replace(",", "", str);
	// convert to integer:
	var num = parseInt(str, 10);
	// if not a number or a negative number then return 0:
	return (isNaN(num) || num < 0) ? 0 : num;
}


function round(val, precision)
{
	// * rounds a val off to a certain val of decimal places
	// * mimics PHP function of same name
	// * val must be a number (integer or float)
	// * precision must be an integer
	// * halves (0.5) always rounded up

	// default value for precision:
	if (precision == null)
		precision = 0;

	if (precision == 0)
		return Math.round(val);
	else if (precision > 0)
	{
		var multiplier = Math.pow(10, precision);
		return Math.round(val * multiplier) / multiplier;
	}
	else // precision < 0
	{
		var multiplier = Math.pow(10, -precision);
		return Math.round(val / multiplier) * multiplier;
	}
}


function roundToNearest(n, m)
{
	// * rounds of n to nearest m
	return m * round(n / m);
}

/**
 * Mimics behaviour of PHP function number_format
 * Adds zeroes to fill decimal places, and adds commas to show thousands, millions, etc.
 * @param float number
 * @param int decimals
 * @param string dec_point
 * @param string thousands_sep
 * @return string
 */
function numberFormat(number, decimals, dec_point, thousands_sep) {
	// default values:
	if (decimals == undefined) {
		decimals = 0;
	}
	if (dec_point == undefined) {
		dec_point = '.';
	}
	if (thousands_sep == undefined) {
		thousands_sep = ',';
	}

	// check number is actually a number:
	number = parseFloat(number);
	if (isNaN(number)) {
		number = 0;
	}
	
	// round off:
	number = round(number, decimals);

	// if number is negative, make it positive first then switch it back later:
	var negative = number < 0;
	if (negative) {
		number = -number;
	}

	// string left of decimal point:
	var left = Math.floor(number);
	if (left == 0) {
		var strLeft = '0';
	}
	else {
		var strLeft = '';
		var left2 = left;
		while (left2 > 0) {
			thousands = left2 % 1000;
			left2 = (left2 - thousands) / 1000;
			if (left2 > 0)
				strLeft = thousands_sep + padLeft(thousands, 3, '0') + strLeft;
			else
				strLeft = thousands + strLeft;
		}
	}
	var result = strLeft;

	// string right of decimal point:
	if (decimals > 0)	{
		var right = number - left;
		right = Math.round(right * Math.pow(10, decimals));
		strRight = padLeft(right, decimals, '0');
		result = strLeft + dec_point + strRight;
	}

	// if number was negative, add sign:
	if (negative) {
		result = '-' + result;
	}

	return result;
}

/**
 * Formats a text field as a number.
 * Min and max values can be specified.
 * The number can be formatted to a certain number of decimal places.
 * 
 * @param	mixed	Can be the id of the textbox, or the textbox object itself.
 * @param	int		decimalPlaces
 * @param	float	min
 * @param	float	max
 * @param	string	defaultValue
 * @return  string	The formatted value.
 */
function formatNumberField(field, decimalPlaces, min, max, defaultValue) {
	// get the object:
	if (typeof field == 'string') {
		field = document.getElementById(field);
	}
	if (!field || field.value == 'undefined') {
		return null;
	}
	// get value:
	var value = parseFloat(field.value);
	if (isNaN(value)) {
		value = (defaultValue === undefined) ? '' : defaultValue;
	} else {
		// limit values to min/max:
		min = parseFloat(min);
		max = parseFloat(max);
		if (!isNaN(min) && value < min)	{
			value = min;
		}
		if (!isNaN(max) && value > max)	{
			value = max;
		}
		// decimal places:
		decimalPlaces = parseInt(decimalPlaces, 10);
		if (!isNaN(decimalPlaces)) {
			value = round(value, decimalPlaces)
		}
	}
	// update text field:
	field.value = value;
	return value;
}


/**
 * @todo add support for larger numbers, and option to use British or US billions, etc.
 */
function numberToWords(num)
{
	// * returns num in words:
	// * supports numbers up to but not including an English billion (1e12)
	// * note, uses English 'thousand million', not American 'billion'
	if (num < 20)
	{
		switch (num)
		{
			case 0: return 'Zero';
			case 1: return 'One';
			case 2: return 'Two';
			case 3: return 'Three';
			case 4: return 'Four';
			case 5: return 'Five';
			case 6: return 'Six';
			case 7: return 'Seven';
			case 8: return 'Eight';
			case 9: return 'Nine';
			case 10: return 'Ten';
			case 11: return 'Eleven';
			case 12: return 'Twelve';
			case 13: return 'Thirteen';
			case 14: return 'Fourteen';
			case 15: return 'Fifteen';
			case 16: return 'Sixteen';
			case 17: return 'Seventeen';
			case 18: return 'Eighteen';
			case 19: return 'Nineteen';
		}
	}
	else if (num < 100)
	{
		var tens = Math.floor(num / 10);
		var units = num - (10 * tens);
		if (units > 0)
			units = '-' + numberToWords(units);
		else
			units = '';
		switch (tens)
		{
			case 2: return 'Twenty' + units;
			case 3: return 'Thirty' + units;
			case 4: return 'Forty' + units;
			case 5: return 'Fifty' + units;
			case 6: return 'Sixty' + units;
			case 7: return 'Seventy' + units;
			case 8: return 'Eighty' + units;
			case 9: return 'Ninety' + units;
		}
	}
	else if (num < 1000)
	{
		var hundreds = Math.floor(num / 100);
		var result = numberToWords(hundreds) + ' Hundred';
		var rem = num - (100 * hundreds);
	}
	else if (num < 1000000)
	{
		var thousands = Math.floor(num / 1000);
		var result = numberToWords(thousands) + ' Thousand';
		var rem = num - (1000 * thousands);
	}
	else if (num < 1e12)
	{
		var millions = Math.floor(num / 1000000);
		var result = numberToWords(millions) + ' Million';
		var rem = num - (1000000 * millions);
	}
	else
		result = 'One Billion or more';
	if (rem > 0)
	{
		if (rem < 100)
			result += ' and ' + numberToWords(rem);
		else
			result += ', ' + numberToWords(rem);
	}
	return result;
}


/**
 * Returns true if val looks like an integer.
 *
 * @param mixed val
 */
function looksLikeInt(val)
{
	return String(val) === String(parseInt(val));
}


/**
 * Integer division.
 * Returns false for invalid input.
 *
 * @param	int	n	numerator
 * @param	int	d	denominator
 * @return	int
 */
function intdiv(n, d)
{
	n = parseInt(n, 10);
	d = parseInt(d, 10);
	if (isNaN(n) || isNaN(d))
	{
		return false;
	}
	// adding 0.5 handles issues with inexact results from the division:
	return Math.floor((n + 0.5) / d);
}


/**
 * Converts a number from one base to another.  Matches PHP function.
 *
 * @param	string	number		The number as a string (if an int, will be converted to string) 
 * @param	int		frombase
 * @param	int		tobase
 * @return	string				The converted number.
 */
function base_convert(number, frombase, tobase)
{
	// @todo

}


var base36Digits = "0123456789abcdefghijklmnopqrstuvwxyz"; 


/**
 * Converts a number from one base to another.
 *
 * @param	string	number		The number as a string (if an int, will be converted to string). 
 * @param	int		frombase	Can be 2..36
 * @return	int					The converted number as a decimal int, or undefined on failure.
 */
function convertToDecimal(n, fromBase)
{
	// ensure n is a string: 
	if (typeof n == 'number')
	{
		n = String(Math.floor(n));	
	}
	else if (typeof n != 'string')
	{
		return undefined;
	}
	// check fromBase is an integer 2..36:
	if (typeof fromBase != 'number' || fromBase != Math.round(fromBase) || fromBase < 2 || fromBase > 36)
	{
		return undefined;
	}
	var result = 0; 
	var ch;
	var m;
	for (var i = 0; i < n.length; i++)
	{
		ch = n.charCodeAt(i);
		if (isDigitCode(ch))
		{
			m = ch - ASCII_0;
		}
		else if (isLowerCaseLetterCode(ch))
		{
			m = ch - ASCII_a + 10; 
		}
		else if (isUpperCaseLetterCode(ch))
		{
			m = ch - ASCII_A + 10;
		}
		else
		{
			return undefined;
		}
		result = result * fromBase + m;
	}
	return result;
}


/**
 * Converts a decimal number (int) to a hexadecimal number (string).
 *
 * @param	int		num
 * @return	string
 */
function dechex(num)
{
	var hexDigits = "0123456789abcdef";
	var hex = "";
	while (num > 0)
	{
		digit = num % 16;
		hex = hexDigits.charAt(digit) + hex;
		num = Math.floor(num / 16);
	}
	return hex;
}
