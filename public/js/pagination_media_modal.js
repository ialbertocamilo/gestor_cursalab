// genera paginacion multimedia
function genera_paginacion(result){
    // prev
    var prev_page = '<li class="page-item disabled" aria-disabled="true" aria-label="« Previous">'+
                                    '<span class="page-link" aria-hidden="true">‹</span>'+
                                '</li>';
    if(result.prev_page_url){
        var prev_page = '<li class="page-item">'+
                            '<a class="page-link pglink" href="'+result.prev_page_url+'" rel="prev" aria-label="« Previous">‹</a>'+
                        '</li>';
    }
    // next
    var next_page = '<li class="page-item disabled" aria-disabled="true" aria-label="Next »">'+
                            '<span class="page-link" aria-hidden="true">›</span>'+
                        '</li>';
    
    if(result.next_page_url){
        next_page = '<li class="page-item">'+
                            '<a class="page-link pglink" href="'+result.next_page_url+'" rel="next" aria-label="Next »">›</a>'+
                        '</li>'; 
    }
    // pages
    var pages = "";
    var min_limite = result.current_page - 3;
    if (min_limite < 1) {
        min_limite = 1;
    }
    var max_limite = result.current_page + 3;
    if (min_limite > 1) {
        pages = pages + '<li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>';
    }

    for (let index = 1; index <= result.last_page; index++) {
    
        if (min_limite <= index && index < result.current_page) {
            if(index == result.current_page){
                pages = pages + '<li class="page-item active" aria-current="page"><span class="page-link">'+result.current_page+'</span></li>';
            }else{
                pages = pages + '<li class="page-item"><a class="page-link pglink" href="'+result.path+'?page='+index+'">'+index+'</a></li>';
            }
        }else if (max_limite >= index && result.current_page <= index) {
            if(index == result.current_page){
                pages = pages + '<li class="page-item active" aria-current="page"><span class="page-link">'+result.current_page+'</span></li>';
            }else{
                pages = pages + '<li class="page-item"><a class="page-link pglink" href="'+result.path+'?page='+index+'">'+index+'</a></li>';
            }
        }
        else{
            if (max_limite < index && max_limite < result.last_page) {
                pages = pages + '<li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>';
                break;
            }
        }
    }

    var pagination = '<ul class="pagination" role="navigation">'+
                        prev_page+
                        pages+
                        next_page+
                    '</ul>';

    return pagination;
}