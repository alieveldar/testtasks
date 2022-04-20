<?php
/**
 * Checks if a specified string doesn’t contain double wildcards
 * @param string $string a string to check
 * @return true, if string doesn’t contain any double wildcard, false otherwise
 */
function checkString(string $string)
{
    return (preg_match('/\*\*/', $string) == 0);
}