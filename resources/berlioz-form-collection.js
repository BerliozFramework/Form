import $ from 'jquery'

const BerliozCollection = (($) => {
    class Collection {
        constructor(target) {
            this.target = $(target);
        }

        addElement() {
            // Count the total element count in the collection
            let newCount = this.target.find('[data-collection-key]').length;
            let newElement = $('<div data-collection-key="' + newCount + '"></div>');
            let prototypeString = this.target.data('prototype').replace(/___name___/g, newCount);
            
            newElement.append($(prototypeString));            
            this.target.append(newElement);
        }

        deleteElement(element) {
            element = $(element);

            // If the element doesn't have the key attribute, it may be a child element of the item, so we try to get
            // the item base element from its parents
            if(!element.data('collection-key')) {
                element = element.parents('[data-collection-key]');
            }

            let collection = $(element.parents('[data-collection]'));
            element.remove();

            // Update the indexes on each element
            let collectionName = collection.data('collection');
            let collectionNameEscaped = this.escapeRegExp(collection.data('collection'));
            let collectionId = collection.attr('id');

            $('[data-collection-key]', collection).each(function(index) {
                $(this).attr('data-collection-key', index);

                $('input, select, textarea, label', this).each(function () {
                    let idPattern = new RegExp(collectionId + '_\\d+');
                    let namePattern = new RegExp(collectionNameEscaped + '\\[\\d+');

                    if ($(this)[0].hasAttribute('for')) {
                        $(this).attr('for', $(this).attr('for').replace(idPattern, collectionId + '_' + index));
                    }

                    if ($(this)[0].hasAttribute('name')) {
                        $(this).attr('name', $(this).attr('name').replace(namePattern, collectionName + '[' + index));
                    }
                    if ($(this)[0].hasAttribute('id')) {
                        $(this).attr('id', $(this).attr('id').replace(idPattern, collectionId + '_' + index));
                    }
                });

            });
        }
        
        escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
        }

        static _jQueryInterface(action, arg1) {
            this.each(function() {
                if(!(typeof $(this).data('collection-object') === 'object' && $(this).data('collection-object') instanceof Collection)) {
                    $(this).data('collection-object', new Collection(this));
                }
    
                switch(action) {
                    case 'add':
                        $(this).data('collection-object').addElement();
                        break;
                    case 'delete':
                        $(this).data('collection-object').deleteElement(arg1);
                        break;
                }
            });
        }
    }

    $.fn.berliozCollection = Collection._jQueryInterface;
    $.fn.berliozCollection.noConflict = function(){
        return Collection._jQueryInterface;
    }
})($);

export default BerliozCollection;