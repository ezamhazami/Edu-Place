# %%
import torch
print("CUDA available:", torch.cuda.is_available())
print("GPU name:", torch.cuda.get_device_name(0) if torch.cuda.is_available() else "CPU")
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

x = torch.rand(10000, 10000, device=device)  # allocated on GPU
print(x.device)




# %%
from langchain_community.llms import LlamaCpp

llm = LlamaCpp(
    model_path=r"C:\Users\ASUS\Downloads\Phi-3-mini-4k-instruct-fp16.gguf",
    n_gpu_layers=-1,   # ✅ all layers on GPU
    max_tokens=500,
    n_ctx=2048,
    seed=42,
    verbose=True       # set True to see loading logs
)

response = llm.invoke("Explain CUDA in one sentence.")
print(response)


# %%
llm.invoke("Hi! My name is Maarten. What is 1 + 1?")

# %%
from langchain import PromptTemplate
from langchain import LLMChain
from langchain.memory import ConversationBufferMemory

# Components
persona = "You are an expert in academic subjects and know everything in every field.\n"
instruction = "Use the conversation history if needed, and answer the question asked.\n"
context = "Your answer must be detailed and provide examples so the student can understand better.\n"
data_format = (
    "Structure your answer as follows:\n"
    "1. Start with a definition or direct answer in 1–2 sentences.\n"
    "   (Add a blank line after this section)\n"
    "2. Add 1–3 sentences of explanation with an example if relevant.\n"
    "   (Add a blank line after this section)\n"
    "3. End with a short bullet-point summary of key takeaways.\n"
    "   (Use one line per bullet, each starting with '-')\n"
    "Keep total length around 3–5 sentences plus the bullets.\n"
)
audience = "The answer is intended for students.\n"
tone = "The tone should be professional and clear.\n"
question_component = "Question to answer: {question}\n"

# Combine into a single template with placeholders
template = persona + instruction + context + data_format + audience + tone + question_component + \
           "Conversation history:\n{chat_history}\n<|assistant|>"

# Define PromptTemplate
prompt = PromptTemplate(
    template=template,
    input_variables=["question", "chat_history"]
)

# Conversation memory
memory = ConversationBufferMemory(memory_key="chat_history")

# Create the LLMChain
llm_chain = LLMChain(
    llm=llm,
    prompt=prompt,
    memory=memory
)

#fuction answer question
def ask_chatbot(question:str) -> str:
    result= llm_chain.invoke({"question":question})
    return result["text"]    

