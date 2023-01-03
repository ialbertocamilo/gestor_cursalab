export default {
    methods: {

        /**
         * Generate string with modules names
         *
         * @param collection
         * @param ids
         */
        generateNamesString(collection, ids) {


            if (collection.length === 0) return ''
            if (!ids) return ''
            if (ids.length === 0) return ''

            let selectedModules = collection.filter(m => ids.includes(m.id));

            let names = [];
            selectedModules.forEach(m => names.push(m.name))

            return names.join('-')
        },
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
