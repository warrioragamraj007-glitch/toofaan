//using mongoose client

const mongoose = require('mongoose');

const uri = 'mongodb://admin:tele123$@ipaddress:27017/admin'; // Replace with your MongoDB URI

mongoose.connect(uri)
  .then(() => {
    console.log('Connected to MongoDB successfully');
    mongoose.connection.close(); // Close connection after test
  })
  .catch((err) => {
    console.error('Error connecting to MongoDB:', err);
  });

