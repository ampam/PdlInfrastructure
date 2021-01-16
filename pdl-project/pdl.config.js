
let mergeConfig = require('./config/mergeConfig');

const config = mergeConfig( {
    companyName: "My Company",
    project: "MiProject",
    version: "1.0.0",
    db2PdlSourceDest: "my-company/my-project/domain/data"
});

module.exports = config;


