from flask import Flask,render_template,request,jsonify
from chatbot import ask_chatbot
from askPdf import askPdf
from summarizer import summarize
import os
import tempfile

app = Flask(__name__)
UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
app.config["UPLOAD_FOLDER"] = UPLOAD_FOLDER

#html
@app.route('/FormChatbot')
def FormChatbot():
    return render_template('FormChatbot.html')

@app.route('/FormPdf')
def  FormPdf():
    return render_template('FormPdf.html')

@app.route('/FormSummarizer')
def FormSummarizer():
    return render_template('FormSummarizer.html')

@app.route('/aiService')
def aiService():
    return render_template('aiService.html')



#ai 
@app.route('/api/chatbot',methods=['POST'])
def chatbot_api():
    question=request.json.get("question")
    answer=ask_chatbot(question)
    return jsonify({"answer":answer})

@app.route('/summarizer',methods=["POST"])
def summarizer():
    user_input=request.json['text']
    answer = summarize(user_input)
    return jsonify({"answer":answer})

@app.route('/ask', methods=['POST'])
def ask_pdf():
    try:
        # Check if files are present
        if 'pdf' not in request.files:
            return jsonify({"error": "No PDF file uploaded"})
        
        pdf_file = request.files['pdf']
        question = request.form.get('question', '').strip()
        
        if pdf_file.filename == '':
            return jsonify({"error": "No PDF file selected"})
            
        if not question:
            return jsonify({"error": "No question provided"})
        
        if pdf_file and pdf_file.filename.lower().endswith('.pdf'):
            # Save uploaded file temporarily
            with tempfile.NamedTemporaryFile(delete=False, suffix='.pdf') as temp_file:
                pdf_file.save(temp_file.name)
                temp_path = temp_file.name
            
            try:
                # Process the PDF and get answer
                result = askPdf(temp_path, question)
                return jsonify(result)
            finally:
                # Clean up temporary file
                try:
                    os.unlink(temp_path)
                except:
                    pass
        else:
            return jsonify({"error": "Please upload a valid PDF file"})
            
    except Exception as e:
        return jsonify({"error": f"Server error: {str(e)}"})

if __name__=="__main__":
    app.run(debug=True)
