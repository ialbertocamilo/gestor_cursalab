import { checkType } from './UtlValidators.js';

/* ==== COMP: @vot/components/VueUploadContentMultinedia ==== */
export function getCurrentExtensionsTypes(types, mixin_extensions) {
	let CurrentExtensions = '';

	const CureExtension = (extensions) => {
		let lineExtension = '';
		for(const value of extensions) {
			lineExtension += `.${value}, `
		}
		return lineExtension;
	};

	for (const value of types) {
		const currValue = mixin_extensions[value];
		CurrentExtensions += CureExtension(currValue); // extensions by key
	}
	return CurrentExtensions; 
}
/* ==== COMP: @vot/components/VueUploadContentMultinedia ==== */

/* ==== COMP: @vot/require/forms/FrmGeneralConfig ==== */
export function createDinamycPayload(currFrm) {
	const payload = {};
	const { isType, isArray } = checkType;

	for(const key in currFrm) {
		const current = currFrm[key];
		const currentParse = isType(current, 'string') ?
												  current.trim() : current;
	  if(currentParse) {
	  	if(isArray(currentParse)) {
	  		if(currentParse.length) payload[key] = currentParse;
				// no array vacio setea
	  	} else payload[key] = currentParse;
	  }
	}

	return payload;
}

export function checkValidModules(stackData, currValKey) {

	const { currData, currValidation } = stackData;
	const ArrayMessages = [];

	for (const prop in currValidation) {
		const curr = currData[prop];
		const currval = currValidation[prop];
		const { required } = currval;
			
		if(!curr && required) ArrayMessages.push(currval);
		const existKey = currValKey.includes(prop);

		if(existKey && required) ArrayMessages.push(currval);
		if(existKey && !required) ArrayMessages.push(currval);
	}

	return ArrayMessages;
}

export function checkValidEmpty(currValidation) {
	const ArrayMessages = [];

	for(const prop in currValidation){
		const curr = currValidation[prop]
		const { required } = curr;
		if(required) ArrayMessages.push(curr);
	}

	return ArrayMessages;
}
/* ==== COMP: @vot/require/forms/FrmGeneralConfig ==== */


/* ==== COMP: @vot/require/VotationsList ==== */
export function getStaticParams(obj) {
	let currStr = '';
	for(const [prop, value] of Object.entries(obj)){
		currStr += `&${prop}=${value}`;
	}
	return currStr;
}
/* ==== COMP: @vot/require/VotationsList ==== */



