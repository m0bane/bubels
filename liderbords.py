from flask import Flask, request, jsonify

app = Flask(__name__)

def update_leaderboard(username, score):
    try:
        with open('leaderboards.txt', 'r') as file:
            leaderboards = file.readlines()

        found = False
        for i, line in enumerate(leaderboards):
            if line.startswith(username):
                found = True
                _, current_score = line.strip().split(';')
                current_score = int(current_score)
                if score > current_score:
                    leaderboards[i] = f'{username};{score}\n'
                break

        if not found:
            leaderboards.append(f'{username};{score}\n')

        with open('leaderboards.txt', 'w') as file:
            file.writelines(leaderboards)

        return True
    except Exception as e:
        print(f'Error updating leaderboard: {e}')
        return False

@app.route('/api/update_score', methods=['POST'])
def update_score():
    try:
        data = request.form
        score = int(data['score'])
        game_over = data['gameOver'] == 'true'
        username = data['username']

        if game_over:
            # Jeśli gra się zakończyła, aktualizuj leaderboard
            if update_leaderboard(username, score):
                return jsonify({'status': 'success'})
            else:
                return jsonify({'status': 'error', 'message': 'Error updating leaderboard'})
        else:
            # Jeśli gra się jeszcze nie zakończyła, możesz dodać dodatkową logikę
            return jsonify({'status': 'success'})

    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)})

if __name__ == '__main__':
    app.run(debug=True)
