import pandas as pd 
import numpy as np 
from flask import Flask, request, jsonify
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import linear_kernel

app = Flask(__name__)

# Load data
lombas_df = pd.read_csv('lombas.csv')
kategoris_df = pd.read_csv('kategoris.csv')
pesertas_df = pd.read_csv('pesertas.csv')

# Merge dataframes lomba dan kategoris
lombas_df = lombas_df.merge(kategoris_df, on='id', how='left')

# Top lomba calculation
lomba_participants = pesertas_df.groupby('idLomba').size().reset_index(name='participant_count')
lombas_df = lombas_df.merge(lomba_participants, left_on='id', right_on='idLomba', how='left')
# lombas_df['participant_count'].fillna(0, inplace=True)
top_lombas = lombas_df.sort_values(by='participant_count', ascending=False).head(5)

# Similarity calculation based on description
tfidf = TfidfVectorizer(stop_words='english')
lombas_df['deskripsiLomba'] = lombas_df['deskripsiLomba'].fillna('')
tfidf_matrix = tfidf.fit_transform(lombas_df['deskripsiLomba'])
cosine_sim = linear_kernel(tfidf_matrix, tfidf_matrix)
indices = pd.Series(lombas_df.index, index=lombas_df['namaLomba']).drop_duplicates()

# Function to recommend top 5 lomba
@app.route('/recommend_popular', methods=['POST'])
def recommend_popular():
    return jsonify(top_lombas['id'].tolist())

# Function to recommend 5 similar lombas based on description
@app.route('/recommend_similar', methods=['POST'])
def recommend_similar():
    data = request.get_json()
    nama_lomba = data.get('namaLomba')
    
    if not nama_lomba or nama_lomba not in indices:
        return jsonify([])  # Return an empty list if namaLomba is not provided or not found

    # Get the index of the lomba that matches the name
    idx = indices[nama_lomba]

    # Get the pairwise similarity scores of all lombas with that lomba
    sim_scores = list(enumerate(cosine_sim[idx]))

    # Sort the lombas based on the similarity scores
    sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

    # Get the indices of the top 5 most similar lombas
    lomba_indices = [i[0] for i in sim_scores[1:6]]  # Mengambil 5 lomba teratas

    # Return the top 5 most similar lombas
    similar_lombas = lombas_df.iloc[lomba_indices]
    
    return jsonify(similar_lombas[['id', 'namaLomba', 'deskripsiLomba', 'posterLomba']].to_dict(orient='records'))

    


# Function to recommend 5 lombas in the same category
@app.route('/recommend_category/<int:id_kategori>', methods=['GET'])
def recommend_category(id_kategori):
    lombas_in_category = lombas_df[lombas_df['idKategori'] == id_kategori].head(5)
    return jsonify(lombas_in_category[['id', 'namaLomba', 'deskripsiLomba', 'posterLomba']].to_dict(orient='records'))


if __name__ == '__main__':
    app.run(debug=True, host="0.0.0.0")
