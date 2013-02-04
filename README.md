Scrub
=====

Clean your incoming channel field data before it hits your database.

## Usage

### Installation
Add the files as per any regular EE add-on, and enable the Scrub Extension

### Configuration

Visit the Scrub extension settings screen and apply filters to each of your fields as required. 

Scrub uses the HTMLawed library, browse the documentation for options for the [Deny Attributes](http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm#s3.4) and [Elements](http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm#s3.3) fields.

The documentation there is a bit crap sorry, I'll be looking at making a more consice set of examples soon.

## Basic Examples

Setting 'Elements' to:

	* -span

Will allow everything except spans. (The asterisk is important)

* * *

Setting 'Deny attributes' to:

	class, style

Will disallow classes and style attributes to all elements.

* * *

Any questions, feature requests etc, please use the issue tracker here on GitHub.