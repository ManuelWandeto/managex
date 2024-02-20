import express from 'express';

const app = express();
app.use(express.json())
app.post('/ipn', (req, res) => {
    console.log("body: ", req.body)
    res.send({...req.body, status: 200})
})

app.listen(4001, ()=> {
    console.log("listening on port 4001");
})