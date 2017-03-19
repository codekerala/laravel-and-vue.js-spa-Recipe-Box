export function toMulipartedForm(form, mode) {
	if(mode === 'edit' && typeof form.image === 'string') {
		const temp = JSON.parse(JSON.stringify(form))
		delete temp.image
		return temp
	} else {
		return objectToFormData(form)
	}
}

export function objectToFormData(obj, form, namespace) {
    let fd = form || new FormData()
    let formKey
    for(var property in obj) {
        if(obj.hasOwnProperty(property)) {
            if(namespace) {
                formKey = namespace + '[' + property + ']';
            } else {
                formKey = property;
            }
            if(obj[property] instanceof Array) {
                for (var i = 0; i < obj[property].length; i++) {
                    objectToFormData(obj[property][i], fd, `${property}[${i}]`);
                }
            } else if(typeof obj[property] === 'object' && !(obj[property] instanceof File)) {
                objectToFormData(obj[property], fd, property);
            } else {
                fd.append(formKey, obj[property]);
            }
        }
    }
    return fd
}
