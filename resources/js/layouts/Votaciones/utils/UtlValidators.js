/* ==== FUNC: setRules ==== */
const StackRules = {
	required() {
		return (v) => !!v || 'Este valor es requerido.';  
	},
	minmax(num) {
		const exist = (num.indexOf('-') >= 0);

		if(exist) {
			const [ first, second ] = num.split('-');

			return (v) => {
				if(v && v.length >= 1 && v.length < first) 
					return `Mínimo ${first} caracteres.`;
				else if(v && v.length > second) 
					return `Máximo ${second} caracteres.`;
				else return true;
			}
		}

		return (v) => (v && v.length >= 1 && v.length < num) ? 
		`El valor debe tener como minimo ${num} caracteres.`: true;
	},
	max(num) {
    return (v) => (v && v.length >= num) || `Mínimo ${num} caracteres.`;
	},
	min(num) {
    return (v) => (v && v.length <= num) || `Máximo ${num} caracteres.`;
	},
	text() {
		return (v) => (v && v.length <= num) || `El valor debe ser menor a ${num} caracteres.`; 
	}
};

export function setRules(...rules) {
	let ArrayRules = [];

	for(const item of rules) {

		let currentCallBack;
		const currentRule = (item.indexOf(':') > -1);

		if(currentRule) {
			const [ method, param ] = item.split(':');
			currentCallBack =	StackRules[method](param); //return callback
			
		} else currentCallBack =	StackRules[item](); //return callback

		ArrayRules.push(currentCallBack);
	}
	return ArrayRules;
}
/* ==== FUNC: setRules ==== */

/* ==== FUNC: Stackvalidations ==== */
export const Stackvalidations = {
	valNumberInt(str, [min, max]){
		const curr = Number(str);
		if(isNaN(curr) || !(Number.isInteger(curr))) return false;

		return (curr >= min && curr <= max)
	},

	valString(str, [min, max]) {
		const curr = str.trim(); 

  	return (curr.length >= min && curr.length <= max);
	},
	valOptString(str, [min, max]) {
		const curr = str.trim();
		const count = curr.length;  

  	return (count >= 1 && count < min && count <= max);
	},
	valObjNull(obj, jumped = []) {
		let st = true;

		for(const prop in obj) {
			if(!jumped.includes(prop)){
				if(obj[prop] === null) st = false;
			}
		}
		
		return st;
	},
	valDomains(str, domains) {
		const { host }  = new URL(str);
		const currParts = host.split('.');

		for(const value of domains) {
			if(currParts.includes(value)) return true;
		}
		return false;
	}
};
/* ==== FUNC: Stackvalidations ==== */

/* ==== FUNC: checkType ==== */
export const checkType = {
	isArray(value) {
		return Array.isArray(value);
	},
	isType(value, type) {
		return (value === null) ? false : (typeof value === type); 
	}
};
/* ==== FUNC: checkType ==== */

/* ==== FUNC: blockType ==== */
export function passOnlyNumbers(evt) {
  evt = (evt) ? evt : window.event;
  let charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode != 9)) {
    evt.preventDefault();
  } else {
    return true;
  }
}
/* ==== FUNC: blockType ==== */
