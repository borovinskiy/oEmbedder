/* 
 * Resize remote embedded resources from ELiS Library
 * It is JS listen of optimal height on iframe and resize iframe
 * @author Arsen I. Borovinskiy
 */

window.addEventListener('message',function(event){
    var data = event.data;
    try {
        if (typeof event.data === 'string') {
            var data = JSON.parse(event.data);
        }
    } catch (err) {
        
    }
    try {
        if (data.height != null && data.src != null) {
            var iframe = document.querySelector("iframe[src='" + data.src + "']");
            if (iframe != null && iframe.src.match(event.origin)) {
                iframe.style.height = data.height + 'px';
                iframe.style.maxHeight = '';
                iframe.style.border = 'none';
            }
        }
    } catch (err) {
       console.error(err);
    }
});
