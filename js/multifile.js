/**
 * Convert a single file-input element into a 'multiple' input list
 *
 * Usage:
 *
 *   1. Create a file input element (no name)
 *      eg. <input type="file" id="first_file_element">
 *
 *   2. Create a DIV for the output to be written to
 *      eg. <div id="files_list"></div>
 *
 *   3. Instantiate a MultiSelector object, passing in the DIV and an (optional) maximum number of files
 *      eg. var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 3 );
 *
 *   4. Add the first element
 *      eg. multi_selector.addElement( document.getElementById( 'first_file_element' ) );
 *
 *   5. That's it.
 *
 *   You might (will) want to play around with the addListRow() method to make the output prettier.
 *
 *   You might also want to change the line 
 *       element.name = 'file_' + this.count;
 *   ...to a naming convention that makes more sense to you.
 * 
 * Licence:
 *   Use this however/wherever you like, just don't blame me if it breaks anything.
 *
 * Credit:
 *   If you're nice, you'll leave this bit:
 *  
 *   Class by Stickman -- http://www.the-stickman.com
 *      with thanks to:
 *      [for Safari fixes]
 *         Luis Torrefranca -- http://www.law.pitt.edu
 *         and
 *         Shawn Parker & John Pennypacker -- http://www.fuzzycoconut.com
 *      [for duplicate name bug]
 *         'neal'
 */
function multiFile(options)
{
    this.listTarget = document.getElementById(options.list); // Where to write the list 
    this.count = 0; // current elements count? 
    this.id = 0;
    this.name = options.name || 'file'; // input name 
    this.extensions = options.extensions || false; // allowed extensions      
    this.limit = options.limit || -1; // is there a maximum?
    this.delimg = options.delimg || '/img/icon-delete.gif'; // delete image src
    
    this.reset = function() {
        this.count = 0;
        this.id = 0;
        this.listTarget.style.display = 'none';
    }
  
    this.clearValue = function(tagId){
        document.getElementById(tagId).innerHTML = 
            document.getElementById(tagId).innerHTML;
    }
  
    //Add a new file input element
    this.addElement = function( element )
    {
        // Make sure it's a file input element
        if( element.tagName == 'INPUT' && element.type == 'file' )
        {
            // Element name -- what number am I?
            element.name = this.name + '_' + this.id++;

            // Add reference to this object
            element.mf = this;
                
            // What to do when a file is selected
            element.onchange = function()
            {
                if(this.mf.extensions && !this.mf.checkExt(this.value))
                {
                    alert('Неверный тип файла'); 
                    return false;    
                }
                
                // New file input
                var new_element = document.createElement( 'input' );
                new_element.type = 'file';
                new_element.className = this.className;

                // Add new element
                this.parentNode.insertBefore( new_element, this );

                // Apply 'update' to element
                this.mf.addElement( new_element );

                new_element.size = element.size;
                
                // Update list
                this.mf.addListRow( this );           
                
                // Hide this: we can't use display:none because Safari doesn't like it
                this.style.position = 'absolute';
                this.style.left = '-1000px';     
            };
            
            // If we've reached maximum number, disable input element
            if( this.limit != -1 && this.count >= this.limit ){
                element.disabled = true;
            }

            // File element counter
            this.count++;
            // Most recent element
            this.current_element = element;
        }
    }

    this.checkExt = function (f) { 
        try
        {                              
             if(!f) return true;
             var ext = f.split(/\.+/).pop().toLowerCase();
             return (this.extensions.search(ext)!=-1);
        } catch(e) { return true; } 
    }
    
    /**
     * Add a new row to the list of files
     */
    this.addListRow = function( element )
    {
        // Row div
        var new_row = document.createElement( 'div' );
        new_row.className = 'clear';
        new_row.element = element;
            
        // Delete button
        var new_row_button = document.createElement( 'div' );
        new_row_button.className = 'link left';
        new_row_button.style.margin = '3px 5px 0 0';
        new_row_button.style.cursor = 'pointer';
        new_row_button.innerHTML = '<img src="'+this.delimg+'" title="удалить" />';    
        
        // References
        var new_row_path = document.createElement( 'div' ); 
        new_row_path.className = 'nowrap left';
        new_row_path.style.margin = '3px 0 0 0';            
        
        // Delete function
        new_row_button.onclick= function()
        {
            // Remove element from form
            this.parentNode.element.parentNode.removeChild( this.parentNode.element );

            // Remove this row from the list
            this.parentNode.parentNode.removeChild( this.parentNode );

            // Decrement counter
            this.parentNode.element.mf.count--;

            // Re-enable input element (if it's disabled)
            this.parentNode.element.mf.current_element.disabled = false;

            if(this.parentNode.element.mf.count <= 1)
                this.parentNode.element.mf.listTarget.style.display = 'none';
            
            // Appease Safari
            //    without it Safari wants to reload the browser window
            //    which nixes your already queued uploads
            return false;
        };

        // Set row value
        new_row_path.innerHTML = element.value.replace(/^([^\\\/]*(\\|\/))*/,"");  

        // Add button
        new_row.appendChild( new_row_button );

        new_row.appendChild ( new_row_path ) 
        // Add it to the list
        this.listTarget.appendChild( new_row );
        this.listTarget.style.display = 'block';
    }
    
    this.addElement( document.getElementById(this.name) );
}; 