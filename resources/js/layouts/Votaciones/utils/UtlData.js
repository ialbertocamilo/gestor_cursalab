import { checkType } from './UtlValidators.js';

/* ==== FUNC: deepAppendFormData ==== */
export function deepAppendFormData(currData) {
	const payload = new FormData();
	const { isArray, isType } = checkType;

	const setValFormData = (key, val) => payload.append(key, val);
	// is file 
	function isFileObject(key, value) {
		const checkFile = (value.type || 'unknown');
		const isFile = (checkFile !== 'unknown');
		
		if(!isFile && isType(value, 'object')) isDeepObject(key, value);
		else payload.append(key, value);
		// else setValFormData(key, value); // set file
	}

	// deep object
	function isDeepObject(key, value) {
		const objectEntries = Object.entries(value);

		for(const [skey, svalue] of objectEntries) {
			const stcKey = `${key}[${skey}]`;

			if(isType(svalue, 'object')) isFileObject(stcKey, svalue);
			else if(isArray(svalue)) isDeepArray(stcKey, svalue);
			else setValFormData(stcKey, svalue);
			// else payload.append(stcKey, svalue);
		}
	}

	// deep array
	function isDeepArray(key, value) {
		for(const svalue of value) {
			const stcKey = `${key}[]`;

			if(isType(svalue, 'object')) isFileObject(stcKey, svalue);
			else if(isArray(svalue)) isDeepArray(stcKey, svalue);
			else setValFormData(stcKey, svalue);
			// else payload.append(stcKey, svalue);
		}
	}

	function initialDeep(currData) {
		const initialEntries = Object.entries(currData); 

		for(const [key, value] of initialEntries) {
			if(value !== null) { //si no es nulo
			  // console.log(key, value);

				if(isType(value, 'object')) isFileObject(key, value);  
				else if(isArray(value)) isDeepArray(key, value); // currValue: []
				else setValFormData(key, value);
				// else payload.append(key, value); // currValue: 'text example'			

			}
		}
	}
	initialDeep(currData);

	return payload;
}
/* ==== FUNC: deepAppendFormData ==== */





