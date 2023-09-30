((Drupal, $) => {
  // Load SpamSpan.
  Drupal.behaviors.spamspan = {
    attach: (context) => {
      // Get each span with class spamspan.
      $("span.spamspan", context).each(function () {
        // Replace each <span class='o'></span> with .
        if ($("span.o", this).length) {
          $("span.o", this).replaceWith(".");
        }

        // For each selected span, set mail to the relevant value, removing
        // spaces.
        const _mail = `${$("span.u", this).text()}@${$(
          "span.d",
          this
        ).text()}`.replace(/\s+/g, "");

        // Build the mailto URI.
        let _mailto = `mailto:${_mail}`;
        if ($("span.h", this).length) {
          // Find the header text, and remove the round brackets from the start
          // and end.
          const _headerText = $("span.h", this)
            .text()
            .replace(/^ ?\((.*)\) ?$/, "$1");
          const _headers = $.map(_headerText.split(/, /), (n) =>
            n.replace(/: /, "=")
          );

          const _headerString = _headers.join("&");
          if (_headerString) {
            _mailto += `?${_headerString}`;
          }
        }

        // Find the anchor content, and remove the round brackets from the
        // start and end.
        let _anchorContent = $("span.t", this).html();
        if (_anchorContent) {
          _anchorContent = _anchorContent.replace(/^ ?\(([^]*)\) ?$/, "$1");
          // Find obfuscated emails in the anchor text and normalize it.
          _anchorContent = _anchorContent.replaceAll("[at]", "@").replaceAll("[dot]", ".");
        }

        // Check if the "span.spamspan" holds any extra attributes from the
        // original <a> tag and put them back after removing 'data-spamspan-'
        // string from the beginning.
        let _attributes = '';
        $.each(this.attributes, function () {
          if (this.specified && this.name.startsWith("data-spamspan-")) {
            _attributes += `${this.name.substring("data-spamspan-".length)}="${this.value}" `;
          }
        });
        // Construct the <a> tag with the extra attributes, if there is any.
        let _tag = "<a></a>";
        if (_attributes) {
          _tag = `<a ${_attributes}></a>`;
        }

        $(this)
          .after(
            $(_tag)
              .attr("href", _mailto)
              .html(_anchorContent || _mail)
              .addClass("spamspan")
          )
          .remove();
      });
    },
  };
})(Drupal, jQuery);
