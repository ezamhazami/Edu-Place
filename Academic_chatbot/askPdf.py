# %%
# ask pdf

# %%
# import model 
from langchain_community.llms import LlamaCpp

llm_pdf=LlamaCpp(
    model_path=r"C:\Users\ASUS\Downloads\Phi-3-mini-4k-instruct-fp16.gguf",
    n_gpu_layers=-1,
    max_tokens=500,
    seed=42,
    verbose=True,
    n_ctx=2048
)
from langchain_huggingface import HuggingFaceEmbeddings
from langchain.chains import RetrievalQA
from langchain import PromptTemplate
from PyPDF2 import PdfReader
from langchain.text_splitter import CharacterTextSplitter
from langchain.vectorstores import FAISS

def askPdf(pdf_path, question):
    # Step 1: Read PDF
    pdf_reader = PdfReader(pdf_path)
    text = ""
    for page in pdf_reader.pages:
        if page.extract_text():
            text += page.extract_text()

    # Step 2: Split into chunks
    text_splitter = CharacterTextSplitter(
        separator="\n",
        chunk_size=1000,
        chunk_overlap=200,
        length_function=len
    )
    chunks = text_splitter.split_text(text)

    # Step 3: Embeddings + FAISS
    embedding_function = HuggingFaceEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")
    Vector_db = FAISS.from_texts(chunks, embedding_function)

    # Step 4: RetrievalQA
    template = """<|user|>
Relevant Information:
{context}

Provide a concise answer to the following question using only the context above:
{question}
<|end|>
<|assistance|>"""

    prompt = PromptTemplate(
        template=template,
        input_variables=["context", "question"]
    )

    rag = RetrievalQA.from_chain_type(
        llm=llm_pdf,
        chain_type="stuff",
        retriever=Vector_db.as_retriever(),
        chain_type_kwargs={"prompt": prompt},
        verbose=False,
        return_source_documents=False
    )

    # Step 5: Run query (new API)
    result = rag.invoke({"query": question})
    return {"answer": result.get("result", "No answer generated")}







