import pandas as pd

# Load data into a Pandas DataFrame
# Replace 'your_data.csv' with the path to your data file
df = pd.read_csv('./data/scores-max=10000-date=15_12_2023.csv')

# Convert DataFrame to Parquet
df.to_parquet('./data/scores-max=10000-date=15_12_2023.parquet')
