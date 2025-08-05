import React, { useState, useEffect } from 'react';
import './App.css';

function App() {
    const [status, setStatus] = useState('Loading...');
    const [articles, setArticles] = useState([]);

    useEffect(() => {
        // Test backend connection
        fetch('/api/health')
            .then(response => response.json())
            .then(data => {
                setStatus('Backend Connected: ' + data.service);
            })
            .catch(error => {
                setStatus('Backend connection failed');
                console.error('Error:', error);
            });

        // Fetch sample articles
        fetch('/api/articles?per_page=5')
            .then(response => response.json())
            .then(data => {
                setArticles(data.data || []);
            })
            .catch(error => {
                console.error('Error fetching articles:', error);
            });
    }, []);

    return (
        <div className="App">
            <header className="App-header">
                <h1>📰 News Aggregator</h1>
                <p className="status">{status}</p>
            </header>

            <main className="App-main">
                <h2>Latest Articles</h2>
                {articles.length > 0 ? (
                    <div className="articles-grid">
                        {articles.map((article, index) => (
                            <div key={index} className="article-card">
                                <h3>{article.title}</h3>
                                <p>{article.description}</p>
                                <small>Source: {article.news_source?.name}</small>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p>No articles available yet. Backend may still be starting up.</p>
                )}
            </main>
        </div>
    );
}

export default App;
