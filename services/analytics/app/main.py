from fastapi import FastAPI

app = FastAPI(
    title="Helmio Analytics API",
    version="0.1.0",
    description="Deterministic financial analytics for Helmio.",
)


@app.get("/health")
def health() -> dict[str, str]:
    return {
        "status": "ok",
        "service": "helmio-analytics",
    }
