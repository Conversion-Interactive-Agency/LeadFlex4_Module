# Formie Custom Email Templates + Fields

* The `default-fields` directory holds the default fields that are available from Verbb.


* To add new field - copy from the `default-fields` directory to the `fields` directory and modify as needed.


* Common template code 
  * `<br/>{{ field.name | t }} - <b>{{ value }}</b>`
    * `<br/>` tag to start
    * ` - `Single space between dash
    * `<b>` open & close tags to wrap value
