const config = {
    "address" : "ws://0.0.0.0:8082",
};
const io = require("socket.io-client");

var socket = io.connect(config.address);

console.log('Started socket client server. Would notify you if connection was extablished');

socket.on('connect', ()=>{
    console.log(20);
})