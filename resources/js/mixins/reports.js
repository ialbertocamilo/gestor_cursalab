export default {
    methods: {

        /**
         * Generate string with collectin's item names
         *
         * @param collection
         * @param ids
         */
        generateNamesString(collection, ids) {

            const names = this.generateNamesArray(collection, ids)
            return names.join(', ')
        }
        ,
        generateNamesArray(collection, ids) {
            if (collection.length === 0) return ''
            if (!ids) return ''
            if (ids.length === 0) return ''

            let selectedModules = collection.filter(m => ids.includes(m.id));

            let names = [];
            selectedModules.forEach(m => {
                if (m.name) {
                    names.push(m.name)
                } else {
                    if (m.title) names.push(m.title)
                }

            })

            return names
        }
        ,
        /**
         * Generate report filename
         */
        generateFilename(prefix, name) {
            return prefix + ' ' +
                name + ' ' +
                new Date().toISOString().slice(0, 10) +
                '.xlsx'
        },
        yesOrNo(booleanValue) {
            return booleanValue ? 'Sí' : 'No'
        }
    }
}
