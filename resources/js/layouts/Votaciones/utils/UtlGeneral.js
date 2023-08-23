const doc = document;

/* ==== FUNC: getImageDimensions ==== */
export function getImageDimensions(path) {
	const domImage = doc.createElement('img');
	
	const promise = new Promise((resolve) => {
		// hook loaded
		domImage.onload = () => {
			const { naturalWidth: width, naturalHeight: height } = domImage;
      resolve({ width, height });
		};
	});

	domImage.src = path;
	return promise;
}
/* ==== FUNC: getImageDimensions ==== */

/* ==== FUNC: getFileSize ==== */
export function getFileSize(size) {
	const units = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  
  let l = 0, 
  		n = parseInt(size, 10) || 0;

  while(n >= 1024 && ++l) {
    n = n/1024;
  }

  return (n.toFixed(n < 10 && l > 0 ? 1 : 0) + ' ' + units[l]);
}

export function resolveToBytes(size, type = 'MB') {
  const types = ['B', 'KB', 'MB', 'GB', 'TB'];
  const key = types.indexOf(type.toUpperCase())
  
  if (typeof key !== "boolean") {
    return size * 1024 ** key; // exponencial
  }
}

export function checkFileSize(file, size) {
	const currentSize = file.size;
	const resolveSize = resolveToBytes(size);

	return (currentSize > resolveSize) ? true : false; 
} 
/* ==== FUNC: getFileSize ==== */

/* ==== FUNC: getFileExtension ==== */
const StackIconsType = {
	link:'fa-link',
	
	// archivos de imagen
	jpg:'fa-file-image', png:'fa-file-image',
	jpeg:'fa-file-image', gif:'fa-file-image',
	
	// archivo pdf
	pdf:'fa-file-pdf',
	
	// archivos de video
	mp4:'fa-file-video', flv:'fa-file-video',
	avi:'fa-file-video', webm: 'fa-file-video',
	mov:'fa-file-video',
	
	// archivos de audio
	mp3:'fa-file-audio', mpeg:'fa-file-audio',

	// archivos excel
	xls:'fa-file-excel', xlsx:'fa-file-excel', 
	csv:'fa-file-excel',
	
	// archivos word
	doc:'fa-file-word', docx:'fa-file-word',

	// archivo power point
	ppt:'fa-file-powerpoint', pptx:'fa-file-powerpoint', 

	// archivos comprimidos 
	zip:'fa-file-archive', scorm:'fa-folder', 
	html:'fa-folder',

	// desconocido 
	unk: 'fa-question'
}

/*	accept=".png, .jpeg, .jpg, 
					.mp4, .mp3, .pdf,
					.doc, .docx, .xls, .xlsx,
					image/gif, image/svg+xml" outlined dense  */
export function getFileExtension(str, key = 'name') { 
	//obtiene la extension by name or type

	const StcExt = (ext) => ({ ext, icon: StackIconsType[ext] || StackIconsType.unk  });
	
	const KeyExt = {
		name(name) {
			const resp = name.toLowerCase().split('.');
			const ext = resp[resp.length - 1];
			return StcExt(ext);
		},
		type(type){
			const [, ext ] = type.split('/');
			return StcExt(ext);
		}
	};

	return KeyExt[key](str); 
}
/* ==== FUNC: getFileExtension ==== */

/* ==== FUNC: focusElementId ==== */
export function focusElementId(id) {
	return setTimeout(() => document.getElementById(id).focus(), 100);
}
/* ==== FUNC: focusElementId ==== */

