# %%
#Summarizer AI

# %%
import torch
print("CUDA available:", torch.cuda.is_available())
print("GPU name:", torch.cuda.get_device_name(0) if torch.cuda.is_available() else "CPU")
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

x = torch.rand(10000, 10000, device=device)  # allocated on GPU
print(x.device)


# %%
from langchain_community.llms import LlamaCpp

llm_summarizer = LlamaCpp(
    model_path=r"C:\Users\ASUS\Downloads\Phi-3-mini-4k-instruct-fp16.gguf",
    n_gpu_layers=-1,   # ✅ all layers on GPU
    max_tokens=500,
    n_ctx=2048,
    seed=42,
    verbose=True       # set True to see loading logs
)

# %%
#prompt
from langchain import PromptTemplate
from langchain import LLMChain

persona = "You are an academic expert skilled at summarizing complex texts clearly and concisely."
data_format = "Summarize in 3–5 sentences."
tone = "Use an academic, clear, and professional tone."
instruction = "Summarize the provided text into a neat, well-structured, and informative explanation."
context = "Ensure the summary is detailed enough for students to understand the key ideas better."
audience = "The summary is intended for students."
texts_to_summarize = "Text to summarize: {texts}"


template=persona+data_format+tone+instruction+context+audience+texts_to_summarize+ "<|assistant|>"

prompt=PromptTemplate(
    template=template,
    input_variables=["texts"]
)

llm_chain_summarizer=LLMChain(
    llm=llm_summarizer,
    prompt=prompt
)

def summarize(text):
    result= llm_chain_summarizer.invoke({"texts":text})
    return result["text"]




# %%
