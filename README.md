# <img src="http://octobercms.com/storage/app/uploads/public/579/a5b/cc1/thumb_5108_64x64_0_0_auto.png" width="60px" valign="center" alt="Content Editor for OctoberCMS"> Content Editor for OctoberCMS

Edit content from front-end.

http://octobercms.com/plugin/samuell-contenteditor

<img src="https://octobercms.com/storage/app/uploads/public/5e1/21f/14d/5e121f14dc10a304078423.png">

### How to use it? It`s simple.

* Drop the Content Editor component to a CMS page.
* Check if you have `{% framework %}` and `{% scripts %}` inside layout for working ajax requests and `{% styles %}` for additional css
* Use this code in your page code and link the editor to a content file or set name to autocreate new file

*Simple example:*
```
{% component 'contenteditor' file="filename_in_content.htm" %}
```

##### Properties

* file - Content block filename to edit, optional. If doesnt exists it will autocreate
* fixture - Fixed name for content block, useful for inline texts (headers, spans...)
* tools - List of enabled tools, comma separated (for all default tools use `*` or leave empty to get all tools defined in settings of Content Editor)
* class - Class for element, mostly useful for fixtures

*Example:*
```
{% component 'contenteditor' file="filename_in_content.htm" fixture="h3" tools="bold,italic" class="my-class" %}
```

##### Tools list

* `bold`           => Bold (b)
* `italic`         => Italic (i)
* `link`           => Link (a)
* `small`          => Small (small)
* `align-left`     => Align left
* `align-center`   => Align center
* `align-right`    => Align right
* `heading`        => Heading (h1)
* `subheading`     => Subheading (h2)
* `subheading3`    => Subheading3 (h3)
* `subheading4`    => Subheading4 (h4)
* `subheading5`    => Subheading5 (h5)
* `paragraph`      => Paragraph (p)
* `unordered-list` => Unordered list (ul)
* `ordered-list`   => Ordered list (ol)
* `table`          => Table
* `indent`         => Indent
* `unindent`       => Unindent
* `line-break`     => Line-break (br)
* `image`          => Image upload
* `video`          => Video
* `preformatted`   => Preformatted (pre)

*Inspired by [Editable plugin](http://octobercms.com/plugin/rainlab-editable) and using Content tools editor  http://getcontenttools.com.*
