Edit content from front-end.

http://octobercms.com/plugin/samuell-contenteditor

### How to use it? It's simple. ###

* Drop the Content Editor component to a CMS page.
* Check if you have `{% framework %}` inside layout for working ajax requests
* Use this code in your page code and link the editor to a content file.

```
{% component 'contenteditor' file="filename_in_content.htm" %}
```

### Planned features ###
* Image upload to storage
* Database saving

*Inspired by [Editable plugin](http://octobercms.com/plugin/rainlab-editable) and using Content tools editor  http://getcontenttools.com.*
