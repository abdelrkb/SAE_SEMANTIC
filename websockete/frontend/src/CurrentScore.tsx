import React from 'react';

interface CurrentScoreProps {
    score: string;
}

const CurrentScore: React.FC<CurrentScoreProps> = ({ score }) => {
    return (
        <div className="CurrentScore">   <h2>Score actuel : </h2> <div className="ScoreValue">{score}</div>        </div>
    );
};

export default CurrentScore;
