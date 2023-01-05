export default {
    methods: {

        /**
         * Generate string with modules names
         *
         * @param modules
         * @param ids
         */
        generateNamesString(modules, ids) {

            let selectedModules = modules.filter(m => ids.includes(m.id));
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
        }
    }
}
