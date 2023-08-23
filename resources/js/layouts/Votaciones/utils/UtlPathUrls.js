export function getParseByUrl(current_url, ...keys) {
	return  new URL(current_url);	
} 

export function getFileUrl(file) {
	return (file) ? URL.createObjectURL(file) : null;
}
